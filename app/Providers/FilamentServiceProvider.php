<?php

namespace App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch->simple();
        });

        // // Add custom CSS
        // Filament::registerRenderHook(
        //     'panels::head.end',
        //     fn (): string => '<link rel="stylesheet" href="' . Vite::asset('resources/css/app.css') . '">'
        // );

        // // Add custom JS
        // Filament::registerRenderHook(
        //     'panels::body.end',
        //     fn (): string => '<script type="module" src="' . Vite::asset('resources/js/app.js') . '"></script>'
        // );
    }
}
