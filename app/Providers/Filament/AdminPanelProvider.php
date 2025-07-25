<?php

namespace App\Providers\Filament;

use App\Services\BreezeCoreService;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use FilipFonal\FilamentLogManager\FilamentLogManager;
use GeoSot\FilamentEnvEditor\FilamentEnvEditorPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Stephenjude\FilamentDebugger\DebuggerPlugin;
use Vormkracht10\FilamentMails\Facades\FilamentMails;
use Vormkracht10\FilamentMails\FilamentMailsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
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
                FilamentShieldPlugin::make(),
                FilamentMailsPlugin::make(),
                BreezeCoreService::make(),
                DebuggerPlugin::make()
                    ->telescopeNavigation(
                        condition: config('telescope.enabled', true),
                        url: config('telescope.path', 'telescope'),
                        openInNewTab: fn () => true,
                    )
                    ->horizonNavigation(false)
                    ->pulseNavigation(false),
                FilamentLogManager::make(),
                FilamentEnvEditorPlugin::make()
                    ->navigationGroup('Settings')
                    ->navigationLabel('Env Editor')
                    ->navigationIcon('heroicon-o-document-text')
                    ->navigationSort(1)
                    ->hideKeys(
                        'APP_KEY',
                        'APP_URL',
                        'DB_PASSWORD',
                        'DB_DATABASE',
                        'DB_HOST',
                        'DB_USERNAME',
                        'DB_CONNECTION',
                        'DB_HOST',
                        'DB_PORT',
                        'MAIL_PASSWORD',
                        'MAIL_USERNAME',
                        'MAIL_HOST',
                        'MAIL_PORT',
                        'MAIL_ENCRYPTION',
                        'MAIL_FROM_ADDRESS',
                        'MAIL_FROM_NAME',
                        'MAIL_MAILER',
                        'MAIL_ENCRYPTION',
                    ),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->routes(fn () => FilamentMails::routes());
    }
}
