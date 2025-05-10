<?php

namespace App\Providers;

use BezhanSalleh\FilamentShield\FilamentShield;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Database\Eloquent\Model;
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
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch->simple();
        });

        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());
        // Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // FilamentShield::prohibitDestructiveCommands($this->app->isProduction());
    }
}
