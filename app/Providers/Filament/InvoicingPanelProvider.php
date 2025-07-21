<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Services\BreezeCoreService;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Invoicing\Pages\Dashboard;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;

class InvoicingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('invoicing')
            ->path('invoicing')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            // ->requiresEmailVerification()
            ->topNavigation()
            ->discoverResources(in: app_path('Filament/Invoicing/Resources'), for: 'App\\Filament\\Invoicing\\Resources')
            ->discoverPages(in: app_path('Filament/Invoicing/Pages'), for: 'App\\Filament\\Invoicing\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Invoicing/Widgets'), for: 'App\\Filament\\Invoicing\\Widgets')
            ->databaseNotifications()
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                EnvironmentIndicatorPlugin::make(),
                BreezeCoreService::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
