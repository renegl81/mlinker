<?php

use App\Http\Controllers\Admin\Core\DashboardController;
use App\Http\Controllers\Admin\Core\UserController;
use App\Http\Controllers\Admin\Menu\AllergenController;
use App\Http\Controllers\Admin\Menu\IngredientController;
use App\Http\Controllers\Admin\Menu\MenuController;
use App\Http\Controllers\Admin\Menu\ProductController;
use App\Http\Controllers\Admin\Menu\QRCodeController;
use App\Http\Controllers\Admin\Tenant\CategoryController;
use App\Http\Controllers\Admin\Tenant\CountryController;
use App\Http\Controllers\Admin\Tenant\LocationController;
use App\Http\Controllers\Admin\Tenant\PlanController;
use App\Http\Controllers\Admin\Tenant\TemplateController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TenantImageController;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/doc', [DocumentationController::class, 'index'])->name('documentation');
Route::get('/tenant_image/{tenant}/{path}', TenantImageController::class)
    ->where('path', '.*')
    ->name('tenant_image');
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Definir las rutas UNA SOLA VEZ - Laravel manejará los dominios automáticamente
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('locations', LocationController::class)->except('create', 'edit');
    Route::resource('categories', CategoryController::class)->except('create', 'edit');
    Route::resource('menus', MenuController::class)->except('create', 'edit');
    Route::resource('products', ProductController::class)->except('create', 'edit');
    Route::resource('ingredients', IngredientController::class)->except('create', 'edit');
    Route::resource('allergens', AllergenController::class)->except('create', 'edit');
    Route::resource('plans', PlanController::class)->except('create', 'edit');
    Route::resource('templates', TemplateController::class)->except('create', 'edit');
    Route::resource('countries', CountryController::class)->except('create', 'edit');
    Route::resource('qrcodes', QRCodeController::class)->except('create', 'edit');
});
