<?php


use App\Http\Controllers\AllergenController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DefaultController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MenuCardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OpeningHourController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [DefaultController::class, 'index'])->name('home');

Route::get('admin', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';


// routes/web.php

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', UserController::class)->except('create', 'edit');
    Route::resource('locations', LocationController::class)->except('create', 'edit');
    Route::resource('categories', CategoryController::class)->except('create', 'edit');
    Route::resource('menu-cards', MenuCardController::class)->except('create', 'edit');
    Route::resource('menus', MenuController::class)->except('create', 'edit');
    Route::resource('sections', SectionController::class)->except('create', 'edit');
    Route::resource('products', ProductController::class)->except('create', 'edit');
    Route::resource('ingredients', IngredientController::class)->except('create', 'edit');
    Route::resource('allergens', AllergenController::class)->except('create', 'edit');
    Route::resource('plans', PlanController::class)->except('create', 'edit');
    Route::resource('subscriptions', SubscriptionController::class)->except('create', 'edit');
    Route::resource('payments', PaymentController::class)->except('create', 'edit');
    Route::resource('templates', TemplateController::class)->except('create', 'edit');
    Route::resource('translations', TranslationController::class)->except('create', 'edit');
    Route::resource('opening-hours', OpeningHourController::class)->except('create', 'edit');
    Route::resource('countries', CountryController::class)->except('create', 'edit');
    Route::resource('qrcodes', QRCodeController::class)->except('create', 'edit');
});
