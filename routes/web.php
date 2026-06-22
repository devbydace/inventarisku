<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes (not authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (require login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.active'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Dashboard (all authenticated users)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard')->middleware('permission:view-dashboard');

    // Stock In/Out (all authenticated users with create-stock-in/out permission)
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/in', \App\Livewire\Stock\In::class)
            ->name('in')->middleware('permission:create-stock-in');

        Route::get('/out', \App\Livewire\Stock\Out::class)
            ->name('out')->middleware('permission:create-stock-out');
    });

    // Stock Adjustment (only admin, manager, audit with permission)
    Route::middleware(['role:admin,manager,audit'])->prefix('stock')->name('stock.')->group(function () {
        Route::get('/adjustment', \App\Livewire\Stock\Adjustment::class)
            ->name('adjustment')->middleware('permission:create-stock-adjustment');
    });

    // Reports (all authenticated users with view-reports permission)
    Route::prefix('reports')->name('reports.')->middleware('permission:view-reports')->group(function () {
        Route::get('/', function () {
            return 'Reports - under construction';
        });
        Route::get('/stock', function () {
            return 'Stock Report - under construction';
        });
        Route::get('/transactions', function () {
            return 'Transaction Report - under construction';
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Master Data Management
    Route::get('/products', \App\Livewire\Product\Index::class)
        ->name('products.index')->middleware('permission:manage-products');

    Route::get('/products/create', \App\Livewire\Product\Create::class)
        ->name('products.create')->middleware('permission:manage-products');

    Route::get('/products/{id}/edit', \App\Livewire\Product\Edit::class)
        ->name('products.edit')->middleware('permission:manage-products');

    Route::get('/categories', \App\Livewire\Category\Index::class)
        ->name('categories.index')->middleware('permission:manage-categories');

    Route::get('/categories/create', \App\Livewire\Category\Create::class)
        ->name('categories.create')->middleware('permission:manage-categories');

    Route::get('/categories/{id}/edit', \App\Livewire\Category\Edit::class)
        ->name('categories.edit')->middleware('permission:manage-categories');

    Route::get('/suppliers', \App\Livewire\Supplier\Index::class)
        ->name('suppliers.index')->middleware('permission:manage-suppliers');

    Route::get('/suppliers/create', \App\Livewire\Supplier\Create::class)
        ->name('suppliers.create')->middleware('permission:manage-suppliers');

    Route::get('/suppliers/{id}/edit', \App\Livewire\Supplier\Edit::class)
        ->name('suppliers.edit')->middleware('permission:manage-suppliers');

    Route::get('/units', \App\Livewire\Unit\Index::class)
        ->name('units.index')->middleware('permission:manage-units');

    Route::get('/units/create', \App\Livewire\Unit\Create::class)
        ->name('units.create')->middleware('permission:manage-units');

    Route::get('/units/{id}/edit', \App\Livewire\Unit\Edit::class)
        ->name('units.edit')->middleware('permission:manage-units');

    // User Management
    Route::get('/users', function () {
        return 'User Management - under construction';
    })->name('users.index')->middleware('permission:manage-users');

    // Settings
    Route::get('/settings', function () {
        return 'Settings - under construction';
    })->name('settings')->middleware('permission:manage-settings');
});

/*
|--------------------------------------------------------------------------
| Manager / Audit Routes
|--------------------------------------------------------------------------
| */
Route::middleware(['auth', 'user.active', 'role:admin,manager,audit'])->prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', \App\Livewire\Approval\Index::class)
        ->name('index')->middleware('permission:approve-transaction');
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});