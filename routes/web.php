<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports — Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Categories
    Route::resource('categories', CategoryController::class);

    // Products
    Route::resource('products', ProductController::class);

    // Stock Management Routes
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/history', [StockController::class, 'history'])->name('history');
        Route::get('/{product}/manage', [StockController::class, 'manage'])->name('manage');
        Route::get('/{product}/preview', [StockController::class, 'preview'])->name('preview');
        Route::post('/{product}/in', [StockController::class, 'stockIn'])->name('in');
        Route::post('/{product}/out', [StockController::class, 'stockOut'])->name('out');
        Route::post('/{product}/adjust', [StockController::class, 'adjust'])
             ->middleware('role:admin')
             ->name('adjust');
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
         ->middleware('role:admin')
         ->name('profile.destroy');
});

require __DIR__.'/auth.php';
