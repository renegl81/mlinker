<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Core\DashboardController;
use App\Http\Controllers\Admin\Tenant\LocationController;
use App\Http\Controllers\Admin\Tenant\MenuController;
use App\Http\Controllers\Admin\Tenant\UserController;
use App\Http\Controllers\Tenant\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitializeTenancyByDomainOptional;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas del tenant
// Usa InitializeTenancyByDomainOptional para que funcione tanto en central como en tenant
Route::middleware([
    'web',
    InitializeTenancyByDomainOptional::class,
])->as('tenant_public.')->group(function () {
    Route::get('/', HomeController::class)->name('tenant_home');
    Route::get('/menu/{menu}', \App\Http\Controllers\Tenant\MenuController::class)->name('tenant_menu_show');

    // Aquí más rutas públicas del tenant en el futuro
});

// Rutas protegidas del tenant (requieren tenant obligatorio)
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->as('tenant.')->group(function () {

    // Rutas protegidas con autenticación
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::prefix('panel')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            Route::resource('users', UserController::class);
            Route::resource('locations', LocationController::class);

            // Solo rutas anidadas para menús
            Route::resource('locations.menus', MenuController::class)->shallow();
        });
    });

});
