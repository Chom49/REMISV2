<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinancialOfficerController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TinVerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public landing – always shown, EXCEPT when the browser was fully closed and
// reopened (viaRemember) or a session lock is already pending, in which case
// the user must re-authenticate on the lock screen first.
// Active-session tabs (new tab while another tab is still open) intentionally
// see the landing page rather than being auto-redirected to the dashboard.
Route::get('/', function () {
    if (Auth::check() && (Auth::viaRemember() || session('auth.locked'))) {
        return redirect()->route('auth.lock');
    }
    return view('landing');
})->name('home');

Route::get('/login',    fn() => redirect('/')->with('open_modal', 'login'))->name('login')->middleware('guest');
Route::get('/register', fn() => redirect('/')->with('open_modal', 'signup'))->name('register')->middleware('guest');

// Auth
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Lock screen – requires auth but no role/locked middleware (prevents redirect loop)
Route::middleware('auth')->group(function () {
    Route::get('/lock',  [AuthController::class, 'showLock'])->name('auth.lock');
    Route::post('/lock', [AuthController::class, 'unlock'])->name('auth.unlock');
});

// Tenant password setup
Route::get('/create-password/{token}',  [PasswordSetupController::class, 'show'])->name('password.setup.show');
Route::post('/create-password',         [PasswordSetupController::class, 'store'])->name('password.setup.store');

// ─────────────────────── LANDLORD ────────────────────────────
Route::middleware(['auth', 'role:landlord', 'locked'])->prefix('landlord')->name('landlord.')->group(function () {

    Route::get('/dashboard', [LandlordController::class, 'dashboard'])->name('dashboard');

    // Properties
    Route::get('/properties',            [LandlordController::class, 'propertiesIndex'])->name('properties.index');
    Route::get('/properties/create',     [LandlordController::class, 'propertiesCreate'])->name('properties.create');
    Route::post('/properties',           [LandlordController::class, 'storeProperty'])->name('properties.store');
    Route::get('/properties/{property}', [LandlordController::class, 'propertiesShow'])->name('properties.show');

    // Unit-based lease routes (new workflow)
    Route::get('/properties/{property}/units/{unit}/leases/create',
               [LandlordController::class, 'unitLeaseCreate'])->name('properties.units.leases.create');
    Route::post('/properties/{property}/units/{unit}/leases',
                [LandlordController::class, 'unitLeaseStore'])->name('properties.units.leases.store');
    Route::post('/properties/{property}/units/{unit}/leases/{lease}/assign-tenant',
                [LandlordController::class, 'unitLeaseAssignTenant'])->name('properties.units.leases.assign-tenant');

    // Leases overview + show + download
    Route::get('/leases',                       [LandlordController::class, 'leasesIndex'])->name('leases.index');
    Route::get('/leases/{lease}',               [LandlordController::class, 'leasesShow'])->name('leases.show');
    Route::get('/leases/{lease}/download',      [LandlordController::class, 'leasesDownload'])->name('leases.download');
    Route::post('/leases/{lease}/send-notice',  [LandlordController::class, 'leasesSendNotice'])->name('leases.send-notice');

    // Legacy lease creation (for backward-compat with single-unit flow)
    Route::get('/properties/{property}/leases/create', [LandlordController::class, 'leasesCreate'])->name('properties.leases.create');
    Route::post('/properties/{property}/leases',       [LandlordController::class, 'leasesStore'])->name('properties.leases.store');

    // Legacy link tenant
    Route::post('/properties/{property}/link-tenant', [LandlordController::class, 'linkTenant'])->name('properties.link-tenant');

    // Lease lifecycle actions
    Route::post('/leases/{lease}/terminate', [LandlordController::class, 'terminateLease'])->name('leases.terminate');
    Route::post('/leases/{lease}/renew',     [LandlordController::class, 'renewLease'])->name('leases.renew');

    // Tenants
    Route::get('/tenants',                  [LandlordController::class, 'tenantsIndex'])->name('tenants.index');
    Route::get('/tenants/create',           [LandlordController::class, 'tenantsCreate'])->name('tenants.create');
    Route::post('/tenants',                 [LandlordController::class, 'tenantsStore'])->name('tenants.store');
    // TIN verification AJAX proxy — must be before /{user} wildcard routes
    Route::post('/tenants/verify-tin',      [TinVerificationController::class, 'verify'])->name('tenants.verify-tin');
    Route::get('/tenants/{user}',           [LandlordController::class, 'tenantsShow'])->name('tenants.show');
    Route::get('/tenants/{user}/edit',      [LandlordController::class, 'tenantsEdit'])->name('tenants.edit');
    Route::patch('/tenants/{user}',         [LandlordController::class, 'tenantsUpdate'])->name('tenants.update');
    Route::post('/tenants/{user}/invite',   [LandlordController::class, 'tenantInvite'])->name('tenants.invite');

    // Maintenance
    Route::get('/maintenance',                          [LandlordController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::post('/maintenance',                         [LandlordController::class, 'maintenanceStore'])->name('maintenance.store');
    Route::patch('/maintenance/{maintenanceRequest}',   [LandlordController::class, 'maintenanceUpdate'])->name('maintenance.update');
    Route::delete('/maintenance/{maintenanceRequest}',  [LandlordController::class, 'maintenanceDestroy'])->name('maintenance.destroy');

    // Reports
    Route::get('/reports',                          [LandlordController::class, 'reportsIndex'])->name('reports.index');
    Route::get('/reports/pdf/rent-payments',        [LandlordController::class, 'reportRentPaymentsPdf'])->name('reports.pdf.rent-payments');
    Route::get('/reports/pdf/tenants',              [LandlordController::class, 'reportTenantsPdf'])->name('reports.pdf.tenants');
    Route::get('/reports/pdf/overdue',              [LandlordController::class, 'reportOverduePdf'])->name('reports.pdf.overdue');
    Route::get('/reports/pdf/properties',           [LandlordController::class, 'reportPropertiesPdf'])->name('reports.pdf.properties');

    // ─── NMB Payments ────────────────────────────────────────────
    Route::get('/payments',                              [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/generate',          [PaymentController::class, 'generateControlNumber'])->name('payments.generate');
    Route::post('/payments/{payment}/send',              [PaymentController::class, 'sendControlNumber'])->name('payments.send');
    Route::get('/payments/{payment}/status',             [PaymentController::class, 'checkStatus'])->name('payments.status');
    Route::get('/payments/poll-all',                     [PaymentController::class, 'pollAll'])->name('payments.poll-all');
    Route::get('/payments/upcoming-count',               [PaymentController::class, 'upcomingCount'])->name('payments.upcoming-count');

    // Settings
    Route::get('/settings',                      [LandlordController::class, 'settingsIndex'])->name('settings.index');
    Route::post('/settings/profile',             [LandlordController::class, 'settingsUpdateProfile'])->name('settings.profile');
    Route::post('/settings/password',            [LandlordController::class, 'settingsUpdatePassword'])->name('settings.password');
    Route::post('/settings/notifications',       [LandlordController::class, 'settingsUpdateNotifications'])->name('settings.notifications');
    Route::post('/settings/preferences',         [LandlordController::class, 'settingsUpdatePreferences'])->name('settings.preferences');
    Route::delete('/settings/picture',           [LandlordController::class, 'settingsRemovePicture'])->name('settings.remove-picture');

    // ─── Financial Officer management ────────────────────────────
    Route::get('/financial-officer',                          [LandlordController::class, 'foIndex'])->name('fo.index');
    Route::get('/financial-officer/create',                   [LandlordController::class, 'foCreate'])->name('fo.create');
    Route::post('/financial-officer',                         [LandlordController::class, 'foStore'])->name('fo.store');
    Route::get('/financial-officer/{fo}/edit',                [LandlordController::class, 'foEdit'])->name('fo.edit');
    Route::patch('/financial-officer/{fo}',                   [LandlordController::class, 'foUpdate'])->name('fo.update');
    Route::post('/financial-officer/{fo}/toggle',             [LandlordController::class, 'foToggle'])->name('fo.toggle');
    Route::delete('/financial-officer/{fo}',                  [LandlordController::class, 'foDestroy'])->name('fo.destroy');
    Route::post('/financial-officer/dismiss-recommendation',  [LandlordController::class, 'foDismissRecommendation'])->name('fo.dismiss-recommendation');
    Route::post('/financial-officer/{fo}/resend-invitation',  [LandlordController::class, 'foResendInvitation'])->name('fo.resend-invitation');
});

// ─────────────────────── ADMIN ───────────────────────────────
Route::middleware(['auth', 'role:admin', 'locked'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Settings
    Route::get('/settings',  [AdminController::class, 'settingsIndex'])->name('settings.index');
    Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');

    // Audit Logs
    Route::get('/audit-logs',   [AdminController::class, 'auditLogsIndex'])->name('audit-logs.index');
    Route::delete('/audit-logs',[AdminController::class, 'auditLogsClear'])->name('audit-logs.clear');

    // Backups
    Route::get('/backups',                        [AdminController::class, 'backupsIndex'])->name('backups.index');
    Route::post('/backups',                       [AdminController::class, 'backupsCreate'])->name('backups.create');
    Route::get('/backups/{backup}/download',      [AdminController::class, 'backupsDownload'])->name('backups.download');
    Route::delete('/backups/{backup}',            [AdminController::class, 'backupsDestroy'])->name('backups.destroy');

    // Users
    Route::get('/users',                          [AdminController::class, 'usersIndex'])->name('users.index');
    Route::patch('/users/{user}/role',            [AdminController::class, 'usersUpdateRole'])->name('users.update-role');
    Route::delete('/users/{user}',                [AdminController::class, 'usersDestroy'])->name('users.destroy');
});

// ─────────────────────── FINANCIAL OFFICER ───────────────────
Route::middleware(['auth', 'role:financial_officer', 'force.password.change', 'locked'])->prefix('fo')->name('fo.')->group(function () {

    Route::get('/dashboard', [FinancialOfficerController::class, 'dashboard'])->name('dashboard');

    // Forced password change
    Route::get('/change-password',  [FinancialOfficerController::class, 'changePasswordShow'])->name('password.change');
    Route::post('/change-password', [FinancialOfficerController::class, 'changePasswordUpdate'])->name('password.update');

    // Payments
    Route::get('/payments',                              [FinancialOfficerController::class, 'paymentsIndex'])->name('payments.index');
    Route::post('/payments/{payment}/generate',          [FinancialOfficerController::class, 'generateControlNumber'])->name('payments.generate');
    Route::post('/payments/{payment}/send',              [FinancialOfficerController::class, 'sendControlNumber'])->name('payments.send');
    Route::get('/payments/{payment}/status',             [FinancialOfficerController::class, 'checkStatus'])->name('payments.status');
    Route::post('/payments/{payment}/mark-paid',         [FinancialOfficerController::class, 'markPaid'])->name('payments.mark-paid');

    // Reports
    Route::get('/reports', [FinancialOfficerController::class, 'reportsIndex'])->name('reports.index');

    // Settings
    Route::get('/settings',               [FinancialOfficerController::class, 'settingsIndex'])->name('settings.index');
    Route::post('/settings/profile',      [FinancialOfficerController::class, 'settingsUpdateProfile'])->name('settings.profile');
    Route::post('/settings/password',     [FinancialOfficerController::class, 'settingsUpdatePassword'])->name('settings.password');
});

// ─────────────────────── TENANT ──────────────────────────────
Route::middleware(['auth', 'role:tenant', 'force.password.change', 'locked'])->prefix('tenant')->name('tenant.')->group(function () {

    Route::get('/dashboard', [TenantController::class, 'dashboard'])->name('dashboard');

    // Forced password change (accessible even while force_password_change = true)
    Route::get('/change-password',  [TenantController::class, 'changePasswordShow'])->name('password.change');
    Route::post('/change-password', [TenantController::class, 'changePasswordUpdate'])->name('password.update');

    // Payments
    Route::get('/payments/history',          [TenantController::class, 'paymentHistory'])->name('payments.history');
    Route::get('/payments/{payment}',        [TenantController::class, 'paymentCheckout'])->name('payments.checkout');
    Route::post('/payments/{payment}/pay',   [TenantController::class, 'paymentPay'])->name('payments.pay');

    // Maintenance
    Route::get('/maintenance',   [TenantController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::post('/maintenance',  [TenantController::class, 'storeMaintenance'])->name('maintenance.store');

    // Settings
    Route::get('/settings',                [TenantController::class, 'settingsIndex'])->name('settings.index');
    Route::post('/settings/profile',       [TenantController::class, 'settingsUpdateProfile'])->name('settings.profile');
    Route::post('/settings/password',      [TenantController::class, 'settingsUpdatePassword'])->name('settings.password');
    Route::post('/settings/notifications', [TenantController::class, 'settingsUpdateNotifications'])->name('settings.notifications');
});
