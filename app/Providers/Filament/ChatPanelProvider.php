<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ChatPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('chat')
            ->login()
            ->registration()
            ->passwordReset()
            ->path('chat')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigation(false)
            ->discoverResources(in: app_path('Filament/Chat/Resources'), for: 'App\\Filament\\Chat\\Resources')
            ->discoverPages(in: app_path('Filament/Chat/Pages'), for: 'App\\Filament\\Chat\\Pages')
            // ->pages([
            //     Pages\Dashboard::class,
            // ])
            // ->discoverWidgets(in: app_path('Filament/Chat/Widgets'), for: 'App\\Filament\\Chat\\Widgets')
            // ->widgets([
            //     Widgets\AccountWidget::class,
            //     Widgets\FilamentInfoWidget::class,
            // ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
