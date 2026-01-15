<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\User;
use App\Policies\AssetPolicy;
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
