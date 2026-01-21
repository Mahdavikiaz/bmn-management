<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\IndicatorQuestion;
use App\Models\Recommendation;
use App\Models\Sparepart;
use App\Models\User;
use App\Policies\AssetPolicy;
use App\Policies\IndicatorQuestionPolicy;
use App\Policies\RecommendationPolicy;
use App\Policies\SparepartPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */

    protected $policies = [
        User::class => UserPolicy::class,
        Asset::class => AssetPolicy::class,
        Sparepart::class => SparepartPolicy::class,
        Recommendation::class => RecommendationPolicy::class,
        IndicatorQuestion::class => IndicatorQuestionPolicy::class,
    ];
    
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
