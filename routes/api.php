<?php

use App\Http\Controllers\Api\V1\ApiLocationController;
use App\Http\Controllers\Api\V1\ApiMenuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public endpoint — no auth required
Route::get('public/menus/{id}', [ApiMenuController::class, 'publicShow'])
    ->name('api.public.menus.show')
    ->middleware('throttle:60,1');

// Authenticated API v1
Route::middleware(['auth:sanctum', 'initialize-tenancy-from-sanctum', 'ensure-api-access'])
    ->prefix('v1')
    ->as('api.v1.')
    ->group(function () {
        Route::get('menus', [ApiMenuController::class, 'index'])->name('menus.index');
        Route::get('menus/{menu}', [ApiMenuController::class, 'show'])->name('menus.show');
        Route::get('menus/{menu}/qr-code', [ApiMenuController::class, 'qrCode'])->name('menus.qr-code');

        Route::get('locations', [ApiLocationController::class, 'index'])->name('locations.index');
        Route::get('locations/{location}', [ApiLocationController::class, 'show'])->name('locations.show');
    });
