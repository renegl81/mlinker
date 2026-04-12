<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Core\DashboardController;
use App\Http\Controllers\Admin\Menu\QRCodeController;
use App\Http\Controllers\Admin\Tenant\BillingController;
use App\Http\Controllers\Admin\Tenant\CatalogIngredientController;
use App\Http\Controllers\Admin\Tenant\CatalogProductController;
use App\Http\Controllers\Admin\Tenant\DocsController;
use App\Http\Controllers\Admin\Tenant\ImageUploadController;
use App\Http\Controllers\Admin\Tenant\LocationController;
use App\Http\Controllers\Admin\Tenant\MenuController;
use App\Http\Controllers\Admin\Tenant\OnboardingController;
use App\Http\Controllers\Admin\Tenant\ProductController;
use App\Http\Controllers\Admin\Tenant\SectionController;
use App\Http\Controllers\Admin\Tenant\TranslationController;
use App\Http\Controllers\Admin\Tenant\UserController;
use App\Http\Controllers\Admin\Tenant\WebsiteSettingsController;
use App\Http\Controllers\Tenant\HomeController;
use App\Http\Middleware\EnsurePlanLimits;
use App\Http\Middleware\InitializeTenancyByDomainOptional;
use App\Http\Middleware\RedirectToOnboarding;
use App\Http\Middleware\SetLocaleFromUrl;
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
});

// Rutas protegidas del tenant (requieren tenant obligatorio)
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->as('tenant.')->group(function () {

    // Rutas protegidas con autenticación
    Route::middleware(['auth', 'verified', SetLocaleFromUrl::class])->group(function () {

        // Rutas de onboarding (FUERA del RedirectToOnboarding para evitar bucles)
        Route::prefix('panel/onboarding')->as('onboarding.')->group(function () {
            Route::get('/', [OnboardingController::class, 'show'])->name('show');
            Route::post('/website', [OnboardingController::class, 'storeWebsite'])->name('website');
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

                // Productos
                Route::get('menus/{menu}/products/create', [ProductController::class, 'create'])->name('menus.products.create');
                Route::post('menus/{menu}/products', [ProductController::class, 'store'])->name('menus.products.store');
                Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
                Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
                Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
                Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');

                // Secciones
                Route::post('menus/{menu}/sections', [SectionController::class, 'store'])->name('menus.sections.store');
                Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
                Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
                Route::post('menus/{menu}/sections/reorder', [SectionController::class, 'reorder'])->name('menus.sections.reorder');

                // QR del menú
                Route::post('menus/{menu}/qr-code', [QRCodeController::class, 'generate'])->name('menus.qr-code.generate');
                Route::get('menus/{menu}/qr-code/download', [QRCodeController::class, 'download'])->name('menus.qr-code.download');
                Route::delete('menus/{menu}/qr-code', [QRCodeController::class, 'destroy'])->name('menus.qr-code.destroy');

                // Traducciones de menú
                Route::get('menus/{menu}/translations', [TranslationController::class, 'show'])->name('menus.translations.show');
                Route::put('menus/{menu}/translations', [TranslationController::class, 'update'])->name('menus.translations.update');
                Route::post('menus/{menu}/translations/add-language', [TranslationController::class, 'addLanguage'])->name('menus.translations.add-language');
                Route::delete('menus/{menu}/translations/language', [TranslationController::class, 'removeLanguage'])->name('menus.translations.remove-language');

                // Catálogo (Business plan o superior)
                Route::prefix('catalog')->as('catalog.')->group(function () {
                    Route::get('products', [CatalogProductController::class, 'index'])->name('products.index');
                    Route::post('products/bulk-delete', [CatalogProductController::class, 'bulkDelete'])->name('products.bulk-delete');
                    Route::post('products/bulk-duplicate', [CatalogProductController::class, 'bulkDuplicate'])->name('products.bulk-duplicate');
                    Route::post('products/bulk-attach-menu', [CatalogProductController::class, 'bulkAttachMenu'])->name('products.bulk-attach-menu');

                    Route::get('ingredients', [CatalogIngredientController::class, 'index'])->name('ingredients.index');
                    Route::put('ingredients/{ingredient}', [CatalogIngredientController::class, 'update'])->name('ingredients.update');
                    Route::put('ingredients/{ingredient}/translations', [CatalogIngredientController::class, 'updateTranslations'])->name('ingredients.translations');
                    Route::delete('ingredients/{ingredient}', [CatalogIngredientController::class, 'destroy'])->name('ingredients.destroy');
                    Route::post('ingredients/merge', [CatalogIngredientController::class, 'merge'])->name('ingredients.merge');
                });

                // Website settings
                Route::prefix('settings')->as('settings.')->group(function () {
                    Route::get('website', [WebsiteSettingsController::class, 'show'])->name('website.show');
                    Route::put('website', [WebsiteSettingsController::class, 'update'])->name('website.update');
                });

                // Docs
                Route::get('docs', [DocsController::class, 'index'])->name('docs.index');
                Route::get('docs/{slug}', [DocsController::class, 'show'])->name('docs.show');

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
