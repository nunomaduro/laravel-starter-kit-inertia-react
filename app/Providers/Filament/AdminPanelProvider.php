<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use A909M\FilamentStateFusion\FilamentStateFusionPlugin;
use App\Http\Middleware\EnsureSetupComplete;
use App\Http\Middleware\FlashOrganizationSwitchNotification;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->font('IBM Plex Sans')
            ->colors([
                'primary' => [
                    50 => 'oklch(0.95 0.05 165)',
                    100 => 'oklch(0.90 0.08 165)',
                    200 => 'oklch(0.82 0.10 165)',
                    300 => 'oklch(0.75 0.12 165)',
                    400 => 'oklch(0.70 0.13 165)',
                    500 => 'oklch(0.65 0.14 165)',
                    600 => 'oklch(0.55 0.14 165)',
                    700 => 'oklch(0.45 0.14 165)',
                    800 => 'oklch(0.38 0.12 165)',
                    900 => 'oklch(0.30 0.10 165)',
                    950 => 'oklch(0.22 0.08 165)',
                ],
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
                NavigationGroup::make('User Management'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Engagement'),
                NavigationGroup::make('Organizations'),
                NavigationGroup::make('Billing'),
            ])
            ->navigationItems([
                NavigationItem::make('System Settings')
                    ->url('/system')
                    ->icon(Heroicon::OutlinedCog8Tooth)
                    ->sort(999)
                    ->visible(fn (): bool => (bool) filament()->auth()->user()?->isSuperAdmin()),
            ])
            ->plugins([
                FilamentStateFusionPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
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
                FlashOrganizationSwitchNotification::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureSetupComplete::class,
            ])
            ->renderHook(PanelsRenderHook::SIDEBAR_LOGO_AFTER, fn (): View => view('filament.components.organization-switcher'))
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START, fn (): View => view('filament.components.back-to-app'))
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn (): View => view('filament.components.admin-panel-sidebar-styles'));
    }
}
