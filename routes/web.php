<?php

use App\Http\Controllers\Admin\AssetSpecificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AssetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\SparepartController;
use App\Http\Controllers\Admin\RecommendationController;
use App\Http\Controllers\Admin\IndicatorQuestionController;
use App\Http\Controllers\Admin\AssetCheckController;
use App\Http\Controllers\Admin\AssetTypeController;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->group(function () {

        // ===== USERS (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\User')->group(function () {
            Route::resource('users', UserController::class);
        });

        // ===== ASSETS (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\Asset')->group(function () {
            Route::resource('assets', AssetController::class);

            Route::get('assets/{asset}/specifications', [AssetSpecificationController::class, 'index'])
                ->name('assets.specifications.index');

            Route::post('assets/{asset}/specifications', [AssetSpecificationController::class, 'store'])
                ->name('assets.specifications.store');

            Route::delete('assets/{asset}/specifications/{spec}', [AssetSpecificationController::class, 'destroy'])
                ->name('assets.specifications.destroy');
        });

        // ===== ASSET TYPES (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\AssetType')->group(function () {
            Route::resource('asset-types', AssetTypeController::class)->except(['show']);
        });

        // ===== SPAREPARTS (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\Sparepart')->group(function () {
            Route::resource('spareparts', SparepartController::class)->except(['show']);
        });

        // ===== RECOMMENDATIONS (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\Recommendation')->group(function () {
            Route::resource('recommendations', RecommendationController::class)->except(['show']);
        });

        // ===== INDICATOR QUESTIONS (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\IndicatorQuestion')->group(function () {
            Route::resource('indicator-questions', IndicatorQuestionController::class)->except(['show']);
        });

        // ===== ASSET CHECKS / PERFORMANCE REPORT (Admin-only) =====
        Route::middleware('can:viewAny,App\Models\PerformanceReport')->group(function () {

            // list asset + status latest check
            Route::get('asset-checks', [AssetCheckController::class, 'index'])
                ->name('asset-checks.index');

            // form pengecekan (buat report baru)
            Route::get('asset-checks/{asset}/create', [AssetCheckController::class, 'create'])
                ->middleware('can:create,App\Models\PerformanceReport')
                ->name('asset-checks.create');

            // submit pengecekan (create report baru)
            Route::post('asset-checks/{asset}', [AssetCheckController::class, 'store'])
                ->middleware('can:create,App\Models\PerformanceReport')
                ->name('asset-checks.store');

            // lihat report tertentu
            Route::get('asset-checks/{asset}/reports/{report}', [AssetCheckController::class, 'show'])
                ->name('asset-checks.show');

            // halaman history report per asset
            Route::get('asset-checks/{asset}/history', [AssetCheckController::class, 'history'])
                ->name('asset-checks.history');

            // hapus report historis
            Route::delete('asset-checks/{asset}/reports/{report}', [AssetCheckController::class, 'destroyReport'])
                ->name('asset-checks.reports.destroy');
        });

    });

});
