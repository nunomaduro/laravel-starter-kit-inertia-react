<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use A909M\FilamentStateFusion\FilamentStateFusionPlugin;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use App\Http\Middleware\EnsureSetupComplete;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stephenjude\FilamentFeatureFlag\FeatureFlagPlugin;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->login()
            ->brandName(config('app.name'))
            ->brandLogo(asset('logo.svg'))
            ->favicon(asset('favicon.svg'))
            ->font('Inter Variable', null, null, [])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->globalSearch()
            ->darkMode()
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->maxContentWidth(Width::SevenExtraLarge)
            ->databaseNotifications()
            ->navigationGroups([
                NavigationGroup::make('User Management'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Engagement'),
                NavigationGroup::make('Organizations'),
                NavigationGroup::make('Billing'),
                NavigationGroup::make('Platform')
                    ->collapsed(),
                NavigationGroup::make('Integrations')
                    ->collapsed(),
                NavigationGroup::make('System')
                    ->collapsed(),
                NavigationGroup::make('Features & Access')
                    ->collapsed(),
                NavigationGroup::make('Content & Legal')
                    ->collapsed(),
            ])
            ->plugins([
                FilamentStateFusionPlugin::make(),
                FeatureFlagPlugin::make(),
                ActivityLogPlugin::make()
                    ->label('Log')
                    ->pluralLabel('Logs')
                    ->navigationGroup('System')
                    ->navigationSort(110),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ->authMiddleware([
                Authenticate::class,
                EnsureSetupComplete::class,
            ]);
    }
}
