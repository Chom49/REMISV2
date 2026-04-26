<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', fn() => view('landing'))->name('home');

// /login and /register redirect to landing with the matching modal pre-opened
Route::get('/login',    fn() => redirect('/')->with('open_modal', 'login'))->name('login')->middleware('guest');
Route::get('/register', fn() => redirect('/')->with('open_modal', 'signup'))->name('register')->middleware('guest');

// Auth
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Tenant password setup (public — arrived via invitation email)
Route::get('/create-password/{token}',  [PasswordSetupController::class, 'show'])->name('password.setup.show');
Route::post('/create-password',         [PasswordSetupController::class, 'store'])->name('password.setup.store');

// ─────────────────────── LANDLORD ────────────────────────────
Route::middleware(['auth', 'role:landlord'])->prefix('landlord')->name('landlord.')->group(function () {

    Route::get('/dashboard', [LandlordController::class, 'dashboard'])->name('dashboard');

    // Properties
    Route::get('/properties',            [LandlordController::class, 'propertiesIndex'])->name('properties.index');
    Route::get('/properties/create',     [LandlordController::class, 'propertiesCreate'])->name('properties.create');
    Route::post('/properties',           [LandlordController::class, 'storeProperty'])->name('properties.store');
    Route::get('/properties/{property}', [LandlordController::class, 'propertiesShow'])->name('properties.show');

    // Leases overview
    Route::get('/leases', [LandlordController::class, 'leasesIndex'])->name('leases.index');

    // Leases (create under a property)
    Route::get('/properties/{property}/leases/create', [LandlordController::class, 'leasesCreate'])->name('properties.leases.create');
    Route::post('/properties/{property}/leases',       [LandlordController::class, 'leasesStore'])->name('properties.leases.store');

    // Link tenant to property
    Route::post('/properties/{property}/link-tenant', [LandlordController::class, 'linkTenant'])->name('properties.link-tenant');

    // Tenants
    Route::get('/tenants',                [LandlordController::class, 'tenantsIndex'])->name('tenants.index');
    Route::get('/tenants/create',         [LandlordController::class, 'tenantsCreate'])->name('tenants.create');
    Route::post('/tenants',               [LandlordController::class, 'tenantsStore'])->name('tenants.store');
    Route::post('/tenants/{user}/invite', [LandlordController::class, 'tenantInvite'])->name('tenants.invite');

    // Maintenance
    Route::get('/maintenance',                          [LandlordController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::post('/maintenance',                         [LandlordController::class, 'maintenanceStore'])->name('maintenance.store');
    Route::patch('/maintenance/{maintenanceRequest}',   [LandlordController::class, 'maintenanceUpdate'])->name('maintenance.update');
    Route::delete('/maintenance/{maintenanceRequest}',  [LandlordController::class, 'maintenanceDestroy'])->name('maintenance.destroy');

    // Reports
    Route::get('/reports', [LandlordController::class, 'reportsIndex'])->name('reports.index');
});

// ─────────────────────── TENANT ──────────────────────────────
Route::middleware(['auth', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {

    Route::get('/dashboard', [TenantController::class, 'dashboard'])->name('dashboard');

    // Payments
    Route::get('/payments/history',          [TenantController::class, 'paymentHistory'])->name('payments.history');
    Route::get('/payments/{payment}',        [TenantController::class, 'paymentCheckout'])->name('payments.checkout');
    Route::post('/payments/{payment}/pay',   [TenantController::class, 'paymentPay'])->name('payments.pay');

    // Maintenance
    Route::get('/maintenance',   [TenantController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::post('/maintenance',  [TenantController::class, 'storeMaintenance'])->name('maintenance.store');
});
