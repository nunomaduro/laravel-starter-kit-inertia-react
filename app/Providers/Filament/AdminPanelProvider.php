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
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
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
                'primary' => Color::Indigo,
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
                NavigationGroup::make('Settings · App')
                    ->collapsed(),
                NavigationGroup::make('Settings · Integrations')
                    ->collapsed(),
                NavigationGroup::make('Settings · System')
                    ->collapsed(),
                NavigationGroup::make('Settings · Features')
                    ->collapsed(),
                NavigationGroup::make('Content & Legal')
                    ->collapsed(),
            ])
            ->navigationItems($this->settingsNavigationItems())
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

    /** @return array<NavigationItem> */
    private function settingsNavigationItems(): array
    {
        $isSuperAdmin = fn (): bool => (bool) filament()->auth()->user()?->isSuperAdmin();

        return [
            // Settings · App
            NavigationItem::make('App')->url('/system/manage-app')->icon(Heroicon::OutlinedCog6Tooth)->group('Settings · App')->sort(10)->visible($isSuperAdmin),
            NavigationItem::make('Auth')->url('/system/manage-auth')->icon(Heroicon::OutlinedShieldCheck)->group('Settings · App')->sort(20)->visible($isSuperAdmin),
            NavigationItem::make('Theme')->url('/system/manage-theme')->icon(Heroicon::OutlinedPaintBrush)->group('Settings · App')->sort(30)->visible($isSuperAdmin),
            NavigationItem::make('SEO')->url('/system/manage-seo')->icon(Heroicon::OutlinedMagnifyingGlass)->group('Settings · App')->sort(40)->visible($isSuperAdmin),
            NavigationItem::make('Cookie Consent')->url('/system/manage-cookie-consent')->icon(Heroicon::OutlinedFingerPrint)->group('Settings · App')->sort(50)->visible($isSuperAdmin),
            NavigationItem::make('Logging')->url('/system/manage-logging')->icon(Heroicon::OutlinedDocumentText)->group('Settings · App')->sort(55)->visible($isSuperAdmin),
            NavigationItem::make('Billing Settings')->url('/system/manage-billing')->icon(Heroicon::OutlinedBanknotes)->group('Settings · App')->sort(60)->visible($isSuperAdmin),
            NavigationItem::make('Tenancy')->url('/system/manage-tenancy')->icon(Heroicon::OutlinedBuildingOffice2)->group('Settings · App')->sort(70)->visible($isSuperAdmin),
            // Settings · Integrations
            NavigationItem::make('Mail')->url('/system/manage-mail')->icon(Heroicon::OutlinedEnvelope)->group('Settings · Integrations')->sort(10)->visible($isSuperAdmin),
            NavigationItem::make('Stripe')->url('/system/manage-stripe')->icon(Heroicon::OutlinedCreditCard)->group('Settings · Integrations')->sort(20)->visible($isSuperAdmin),
            NavigationItem::make('Paddle')->url('/system/manage-paddle')->icon(Heroicon::OutlinedCurrencyDollar)->group('Settings · Integrations')->sort(30)->visible($isSuperAdmin),
            NavigationItem::make('Lemon Squeezy')->url('/system/manage-lemon-squeezy')->icon(Heroicon::OutlinedReceiptPercent)->group('Settings · Integrations')->sort(40)->visible($isSuperAdmin),
            NavigationItem::make('Prism')->url('/system/manage-prism')->icon(Heroicon::OutlinedSparkles)->group('Settings · Integrations')->sort(50)->visible($isSuperAdmin),
            NavigationItem::make('AI')->url('/system/manage-ai')->icon(Heroicon::OutlinedCpuChip)->group('Settings · Integrations')->sort(60)->visible($isSuperAdmin),
            NavigationItem::make('Broadcasting')->url('/system/manage-broadcasting')->icon(Heroicon::OutlinedSignal)->group('Settings · Integrations')->sort(70)->visible($isSuperAdmin),
            NavigationItem::make('Integrations')->url('/system/manage-integrations')->icon(Heroicon::OutlinedPuzzlePiece)->group('Settings · Integrations')->sort(80)->visible($isSuperAdmin),
            // System
            NavigationItem::make('Backup')->url('/system/manage-backup')->icon(Heroicon::OutlinedCloudArrowUp)->group('Settings · System')->sort(10)->visible($isSuperAdmin),
            NavigationItem::make('Infrastructure')->url('/system/manage-infrastructure')->icon(Heroicon::OutlinedServer)->group('Settings · System')->sort(15)->visible($isSuperAdmin),
            NavigationItem::make('Search')->url('/system/manage-scout')->icon(Heroicon::OutlinedDocumentMagnifyingGlass)->group('Settings · System')->sort(20)->visible($isSuperAdmin),
            NavigationItem::make('Media')->url('/system/manage-media')->icon(Heroicon::OutlinedPhoto)->group('Settings · System')->sort(30)->visible($isSuperAdmin),
            NavigationItem::make('Filesystem')->url('/system/manage-filesystem')->icon(Heroicon::OutlinedFolderOpen)->group('Settings · System')->sort(40)->visible($isSuperAdmin),
            NavigationItem::make('Security')->url('/system/manage-security')->icon(Heroicon::OutlinedShieldExclamation)->group('Settings · System')->sort(50)->visible($isSuperAdmin),
            NavigationItem::make('Performance')->url('/system/manage-performance')->icon(Heroicon::OutlinedBolt)->group('Settings · System')->sort(60)->visible($isSuperAdmin),
            NavigationItem::make('Monitoring')->url('/system/manage-monitoring')->icon(Heroicon::OutlinedChartBar)->group('Settings · System')->sort(70)->visible($isSuperAdmin),
            NavigationItem::make('Memory')->url('/system/manage-memory')->icon(Heroicon::OutlinedCircleStack)->group('Settings · System')->sort(80)->visible($isSuperAdmin),
            NavigationItem::make('Organization Overrides')->url('/system/manage-organization-overrides')->icon(Heroicon::OutlinedBuildingOffice)->group('Settings · System')->sort(120)->visible($isSuperAdmin),
            // Settings · Features
            NavigationItem::make('Feature Flags')->url('/system/manage-feature-flags')->icon(Heroicon::OutlinedFlag)->group('Settings · Features')->sort(10)->visible($isSuperAdmin),
            NavigationItem::make('Permissions')->url('/system/manage-permissions')->icon(Heroicon::OutlinedLockClosed)->group('Settings · Features')->sort(30)->visible($isSuperAdmin),
            NavigationItem::make('Impersonate')->url('/system/manage-impersonate')->icon(Heroicon::OutlinedUserCircle)->group('Settings · Features')->sort(40)->visible($isSuperAdmin),
            NavigationItem::make('Activity Log')->url('/system/manage-activity-log')->icon(Heroicon::OutlinedClipboardDocumentList)->group('Settings · Features')->sort(50)->visible($isSuperAdmin),
        ];
    }
}
