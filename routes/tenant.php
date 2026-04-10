<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Core\DashboardController;
use App\Http\Controllers\Admin\Menu\QRCodeController;
use App\Http\Controllers\Admin\Tenant\BillingController;
use App\Http\Controllers\Admin\Tenant\ImageUploadController;
use App\Http\Controllers\Admin\Tenant\LocationController;
use App\Http\Controllers\Admin\Tenant\MenuController;
use App\Http\Controllers\Admin\Tenant\OnboardingController;
use App\Http\Controllers\Admin\Tenant\ProductController;
use App\Http\Controllers\Admin\Tenant\TranslationController;
use App\Http\Controllers\Admin\Tenant\UserController;
use App\Http\Controllers\Tenant\HomeController;
use App\Http\Middleware\EnsurePlanLimits;
use App\Http\Middleware\InitializeTenancyByDomainOptional;
use App\Http\Middleware\RedirectToOnboarding;
use Illuminate\Support\Facades\Route;
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
    Route::get('/menu/{menu}', App\Http\Controllers\Tenant\MenuController::class)->name('tenant_menu_show');
    Route::get('/m/{menu}', App\Http\Controllers\Tenant\MenuController::class)->name('tenant_menu_short');

    Route::prefix('menus/{menu}')->group(function () {
        Route::post('/products', [ProductController::class, 'store'])
            ->name('tenant.menus.products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->name('tenant.menus.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('tenant.menus.products.destroy');
    });
});

// Rutas protegidas del tenant (requieren tenant obligatorio)
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->as('tenant.')->group(function () {

    // Rutas protegidas con autenticación
    Route::middleware(['auth', 'verified'])->group(function () {

        // Rutas de onboarding (FUERA del RedirectToOnboarding para evitar bucles)
        Route::prefix('panel/onboarding')->as('onboarding.')->group(function () {
            Route::get('/', [OnboardingController::class, 'show'])->name('show');
            Route::post('/location', [OnboardingController::class, 'storeLocation'])->name('location');
            Route::post('/menu', [OnboardingController::class, 'storeMenu'])->name('menu');
            Route::post('/products', [OnboardingController::class, 'storeProducts'])->name('products');
            Route::post('/complete', [OnboardingController::class, 'complete'])->name('complete');
        });

        // Resto del panel con plan limits y redirect al onboarding si no completado
        Route::middleware([EnsurePlanLimits::class, RedirectToOnboarding::class])->group(function () {
            Route::prefix('panel')->group(function () {
                Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

                Route::resource('users', UserController::class);
                Route::resource('locations', LocationController::class);

                // Solo rutas anidadas para menús
                Route::resource('locations.menus', MenuController::class)->shallow();

                // Subida de imágenes
                Route::post('uploads/image', [ImageUploadController::class, 'store'])->name('uploads.image');

                // QR del menú
                Route::post('menus/{menu}/qr-code', [QRCodeController::class, 'generate'])->name('menus.qr-code.generate');
                Route::get('menus/{menu}/qr-code/download', [QRCodeController::class, 'download'])->name('menus.qr-code.download');
                Route::delete('menus/{menu}/qr-code', [QRCodeController::class, 'destroy'])->name('menus.qr-code.destroy');

                // Traducciones de menú
                Route::get('menus/{menu}/translations', [TranslationController::class, 'show'])->name('menus.translations.show');
                Route::put('menus/{menu}/translations', [TranslationController::class, 'update'])->name('menus.translations.update');

                // Billing
                Route::prefix('billing')->as('billing.')->group(function () {
                    Route::get('plans', [BillingController::class, 'plans'])->name('plans');
                    Route::post('checkout', [BillingController::class, 'checkout'])->name('checkout');
                    Route::get('success', [BillingController::class, 'success'])->name('success');
                    Route::get('manage', [BillingController::class, 'manage'])->name('manage');
                    Route::post('cancel', [BillingController::class, 'cancel'])->name('cancel');
                    Route::post('resume', [BillingController::class, 'resume'])->name('resume');
                });
            });
        });
    });

});
