<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\PasswordSetupController;
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
    Route::get('/reports', [LandlordController::class, 'reportsIndex'])->name('reports.index');
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
});
