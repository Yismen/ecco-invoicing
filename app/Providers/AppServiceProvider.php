<?php

namespace App\Providers;

use App\Models\User;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventAccessingMissingAttributes(! app()->isProduction());
        // Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        FilamentShield::prohibitDestructiveCommands(app()->isProduction());

        // \Livewire\Livewire::component('sanctum_tokens', \Jeffgreco13\FilamentBreezy\Livewire\SanctumTokens::class);

        Gate::before(function (User $user) {
            return true;
        });
    }
}
