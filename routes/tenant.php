<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Core\DashboardController;
use App\Http\Controllers\Admin\Tenant\MenuController;
use App\Http\Controllers\Admin\Tenant\TemplateController;
use App\Http\Controllers\Admin\Tenant\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'auth',
    'verified'
])->as('tenant.')->group(function () {
    Route::prefix('panel')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('menus', MenuController::class);
        Route::resource('users', UserController::class);
    });

});
