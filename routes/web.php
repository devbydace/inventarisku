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
        Route::get('/in', function () {
            return 'Stock In - under construction';
        })->middleware('permission:create-stock-in');

        Route::get('/out', function () {
            return 'Stock Out - under construction';
        })->middleware('permission:create-stock-out');

        Route::get('/adjustment', function () {
            return 'Stock Adjustment - under construction';
        })->middleware('permission:create-stock-adjustment');
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
    Route::get('/products', function () {
        return 'Products - under construction';
    })->name('products.index')->middleware('permission:manage-products');

    Route::get('/categories', function () {
        return 'Categories - under construction';
    })->name('categories.index')->middleware('permission:manage-categories');

    Route::get('/suppliers', function () {
        return 'Suppliers - under construction';
    })->name('suppliers.index')->middleware('permission:manage-suppliers');

    Route::get('/units', function () {
        return 'Units - under construction';
    })->name('units.index')->middleware('permission:manage-units');

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
    Route::get('/', function () {
        return 'Approvals - under construction';
    })->middleware('permission:approve-transaction');
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});