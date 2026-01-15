<?php

use App\Http\Controllers\Admin\AssetSpecificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AssetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->group(function () {
        // ===== USERS (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\User')->group(function () {
            Route::resource('users', UserController::class);
        });

        // ===== ASSETS (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\Asset')->group(function () {
            Route::resource('assets', AssetController::class);

            /**
             * Kelola Spesifikasi (History)
             * - GET  admin/assets/{asset}/specifications  -> form & history
             * - POST admin/assets/{asset}/specifications  -> simpan spec versi baru (insert row)
             */
            Route::get('assets/{asset}/specifications', [AssetSpecificationController::class, 'index'])
                ->name('assets.specifications.index');

            Route::post('assets/{asset}/specifications', [AssetSpecificationController::class, 'store'])
                ->name('assets.specifications.store');
        });
    });

});



