<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use A909M\FilamentStateFusion\FilamentStateFusionPlugin;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use App\Http\Middleware\EnsureSetupComplete;
use App\Http\Middleware\EnsureSuperAdmin;
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
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stephenjude\FilamentFeatureFlag\FeatureFlagPlugin;

final class SystemPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('system')
            ->path('system')
            ->authGuard('web')
            ->login()
            ->brandName(config('app.name').' · System')
            ->brandLogo(asset('logo.svg'))
            ->favicon(asset('favicon.svg'))
            ->font('Inter Variable', null, null, [])
            ->colors([
                'primary' => Color::Violet,
            ])
            ->globalSearch()
            ->darkMode()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('14rem')
            ->collapsedSidebarWidth('3.5rem')
            ->spa()
            ->maxContentWidth(Width::Full)
            ->databaseNotifications()
            ->navigationGroups([
                NavigationGroup::make('Settings · App'),
                NavigationGroup::make('Organizations'),
                NavigationGroup::make('Billing'),
                NavigationGroup::make('Settings · Integrations')
                    ->collapsed(),
                NavigationGroup::make('Settings · System')
                    ->collapsed(),
                NavigationGroup::make('Settings · Features')
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
                    ->navigationGroup('Settings · System')
                    ->navigationSort(110),
            ])
            ->discoverResources(in: app_path('Filament/System/Resources'), for: 'App\Filament\System\Resources')
            ->when(config('modules.announcements'), fn (Panel $p) => $p->discoverResources(in: base_path('modules/announcements/src/Filament/Resources'), for: 'Modules\Announcements\Filament\Resources'))
            ->when(config('modules.blog'), fn (Panel $p) => $p->discoverResources(in: base_path('modules/blog/src/Filament/Resources'), for: 'Modules\Blog\Filament\Resources'))
            ->when(config('modules.changelog'), fn (Panel $p) => $p->discoverResources(in: base_path('modules/changelog/src/Filament/Resources'), for: 'Modules\Changelog\Filament\Resources'))
            ->when(config('modules.contact'), fn (Panel $p) => $p->discoverResources(in: base_path('modules/contact/src/Filament/Resources'), for: 'Modules\Contact\Filament\Resources'))
            ->when(config('modules.help'), fn (Panel $p) => $p->discoverResources(in: base_path('modules/help/src/Filament/Resources'), for: 'Modules\Help\Filament\Resources'))
            ->discoverPages(in: app_path('Filament/System/Pages'), for: 'App\Filament\System\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/System/Widgets'), for: 'App\Filament\System\Widgets')
            ->when(config('modules.gamification'), fn (Panel $p) => $p->discoverWidgets(in: base_path('modules/gamification/src/Filament/Widgets'), for: 'Modules\Gamification\Filament\Widgets'))
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
                EnsureSuperAdmin::class,
            ])
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START, fn (): View => view('filament.components.back-to-app'))
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn (): View => view('filament.components.system-panel-sidebar-styles'));
    }
}
