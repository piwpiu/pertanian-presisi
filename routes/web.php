<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

Route::get('/', [DashboardController::class, 'index']);

// Admin Routes
Route::prefix('admin')->group(function () {
    // Login routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminController::class, 'loginPage'])->name('admin.login');
        Route::post('/login', [AdminController::class, 'login']);
    });

    // Protected admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/form/{id?}', [AdminController::class, 'edit'])->name('admin.form');
        Route::post('/store', [AdminController::class, 'store'])->name('admin.store');
        Route::delete('/delete/{id}', [AdminController::class, 'delete'])->name('admin.delete');
        Route::post('/generate-prediksi', [AdminController::class, 'generatePrediksi'])
            ->name('admin.generate-prediksi');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    });
});

// Melihat cache OpenWeather (hanya di lokal)
Route::get('/debug/weather-cache', function () {
    abort_unless(
        App::environment('local'),
        403
    );

    return response()->json(
        Cache::get('openweather.current_weather')
    );
});