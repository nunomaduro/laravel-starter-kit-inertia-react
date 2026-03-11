<?php

declare(strict_types=1);

namespace App\Providers;

use App\DataTables\UserDataTable;
use App\Events\OrganizationMemberAdded;
use App\Events\OrganizationMemberRemoved;
use App\Events\User\UserCreated;
use App\Listeners\Billing\AddCreditsFromLemonSqueezyOrder;
use App\Listeners\Billing\SyncSubscriptionSeatsOnMemberChange;
use App\Listeners\CreatePersonalOrganizationOnUserCreated;
use App\Listeners\Gamification\GrantGamificationOnUserCreated;
use App\Listeners\LogImpersonationEvents;
use App\Listeners\MigrationListener;
use App\Listeners\SendSlackAlertOnJobFailed;
use App\Models\Shareable;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use App\Observers\PermissionActivityObserver;
use App\Observers\RoleActivityObserver;
use App\Observers\UserObserver;
use App\Policies\ShareablePolicy;
use App\Services\PaymentGateway\PaymentGatewayManager;
use App\Services\PrismService;
use App\Settings\AuthSettings;
use App\Settings\SeoSettings;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use LemonSqueezy\Laravel\Events\OrderCreated;
use Machour\DataTable\Http\Controllers\DataTableDetailRowController;
use Machour\DataTable\Http\Controllers\DataTableExportController;
use Machour\DataTable\Http\Controllers\DataTableImportController;
use Machour\DataTable\Http\Controllers\DataTableInlineEditController;
use Machour\DataTable\Http\Controllers\DataTableSelectAllController;
use Machour\DataTable\Http\Controllers\DataTableToggleController;
use Pan\PanConfiguration;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\CauserResolver as ActivitylogCauserResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use STS\FilamentImpersonate\Events\EnterImpersonation;
use STS\FilamentImpersonate\Events\LeaveImpersonation;
use Throwable;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(PrismService::class, fn (): PrismService => new PrismService);

        if (class_exists(\Essa\APIToolKit\Exceptions\Handler::class)) {
            $this->app->singleton(ExceptionHandler::class, \Essa\APIToolKit\Exceptions\Handler::class);
        }

        $this->app->singleton(PaymentGatewayManager::class);

        config(['filament-impersonate.redirect_to' => '/dashboard']);
    }

    public function boot(): void
    {
        $this->configurePasswordDefaults();
        $this->configurePan();

        $this->registerSeoViewComposer();
        $this->registerActivityLogTaps();
        $this->registerActivityLogImpersonationCauser();

        Gate::policy(Shareable::class, ShareablePolicy::class);

        Gate::before(function ($user, string $ability, array $arguments): ?bool {
            if (! $user) {
                return null;
            }

            if (! $this->userHasBypassPermissions($user)) {
                return null;
            }

            if ($this->isUserModelDangerousOperation($ability, $arguments)) {
                return null;
            }

            return true;
        });

        if (config('seeding.auto_sync_after_migrations', true)) {
            Event::listen(MigrationsEnded::class, MigrationListener::class);
        }

        Event::listen(EnterImpersonation::class, [LogImpersonationEvents::class, 'handleEnterImpersonation']);
        Event::listen(LeaveImpersonation::class, [LogImpersonationEvents::class, 'handleLeaveImpersonation']);
        Event::listen(JobFailed::class, SendSlackAlertOnJobFailed::class);
        Event::listen(UserCreated::class, GrantGamificationOnUserCreated::class);
        Event::listen(UserCreated::class, CreatePersonalOrganizationOnUserCreated::class);
        Event::listen(OrganizationMemberAdded::class, SyncSubscriptionSeatsOnMemberChange::class);
        Event::listen(OrganizationMemberRemoved::class, SyncSubscriptionSeatsOnMemberChange::class);
        Event::listen(OrderCreated::class, AddCreditsFromLemonSqueezyOrder::class);

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event): void {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
            $event->extendSocialite('github', \SocialiteProviders\GitHub\Provider::class);
        });

        User::observe(UserObserver::class);

        foreach ([
            DataTableExportController::class,
            DataTableInlineEditController::class,
            DataTableToggleController::class,
            DataTableSelectAllController::class,
            DataTableDetailRowController::class,
            DataTableImportController::class,
        ] as $controller) {
            $controller::register('users', UserDataTable::class);
        }
    }

    private function userHasBypassPermissions(object $user): bool
    {
        return (bool) DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('permissions.name', 'bypass-permissions')
            ->where('model_has_permissions.model_id', $user->getKey())
            ->where('model_has_permissions.model_type', $user::class)
            ->exists()
            || DB::table('model_has_roles')
                ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('permissions.name', 'bypass-permissions')
                ->where('model_has_roles.model_id', $user->getKey())
                ->where('model_has_roles.model_type', $user::class)
                ->exists();
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    private function isUserModelDangerousOperation(string $ability, array $arguments): bool
    {
        if (! in_array($ability, ['delete', 'forceDelete'], true)) {
            return false;
        }

        $model = $arguments[0] ?? null;

        return $model instanceof User;
    }

    /**
     * Configure Password::defaults() based on the DB-backed password policy in AuthSettings.
     * All form requests that use Password::defaults() will automatically pick up this policy.
     */
    private function configurePasswordDefaults(): void
    {
        Password::defaults(function (): Password {
            try {
                $auth = resolve(AuthSettings::class);
                $rule = Password::min($auth->password_min_length > 0 ? $auth->password_min_length : 8);

                if ($auth->password_require_uppercase) {
                    $rule = $rule->mixedCase();
                }

                if ($auth->password_require_numbers) {
                    $rule = $rule->numbers();
                }

                if ($auth->password_require_symbols) {
                    return $rule->symbols();
                }

                return $rule;
            } catch (Throwable) {
                return Password::min(8);
            }
        });
    }

    private function configurePan(): void
    {
        PanConfiguration::allowedAnalytics([
            'announcements-banner',
            'settings-nav-profile',
            'settings-nav-password',
            'settings-nav-two-factor',
            'settings-nav-appearance',
            'settings-nav-branding',
            'settings-nav-features',
            'settings-nav-roles',
            'settings-nav-audit-log',
            'settings-nav-notifications',
            'settings-nav-data-export',
            'settings-nav-achievements',
            'settings-nav-onboarding',
            'appearance-tab-light',
            'appearance-tab-dark',
            'appearance-tab-system',
            'auth-login-button',
            'auth-google-login',
            'auth-github-login',
            'auth-sign-up-link',
            'auth-register-button',
            'auth-log-in-link',
            'auth-forgot-password-button',
            'welcome-dashboard',
            'welcome-log-in',
            'welcome-register',
            'welcome-blog',
            'welcome-changelog',
            'welcome-help',
            'welcome-contact',
            'welcome-feature-orgs',
            'welcome-feature-billing',
            'welcome-feature-ai',
            'welcome-feature-rbac',
            'welcome-feature-2fa',
            'welcome-feature-analytics',
            'welcome-feature-audit-log',
            'welcome-feature-flags',
            'welcome-feature-custom-roles',
            'welcome-feature-credits',
            'welcome-feature-mcp',
            'welcome-feature-workflows',
            'welcome-feature-email-templates',
            'welcome-feature-blog',
            'welcome-feature-help',
            'welcome-feature-search',
            'welcome-feature-theming',
            'welcome-feature-page-builder',
            'welcome-feature-gamification',
            'welcome-feature-datatables',
            'welcome-feature-api',
            'welcome-feature-data-export',
            'welcome-feature-domains',
            'welcome-feature-social-login',
            'welcome-feature-notifications',
            'welcome-feature-announcements',
            'welcome-feature-impersonation',
            'welcome-feature-visibility-sharing',
            'welcome-feature-backups',
            'welcome-feature-installer',
            'nav-dashboard',
            'nav-organizations',
            'nav-billing',
            'nav-blog',
            'nav-changelog',
            'nav-help',
            'nav-contact',
            'nav-api-docs',
            'nav-repository',
            'nav-documentation',
            'dashboard-quick-edit-profile',
            'dashboard-quick-settings',
            'dashboard-quick-export-pdf',
            'dashboard-quick-contact',
            'dashboard-quick-email-templates',
            'dashboard-quick-product-analytics',
            'dashboard-quick-horizon',
            'dashboard-quick-waterline',
            'dashboard-quick-telescope',
            'dashboard-card-view-analytics',
            'dashboard-admin-users',
            'dashboard-admin-orgs',
            'dashboard-admin-contact',
            'onboarding-get-started',
            'command-palette',
            'global-search',
            'nav-chat',
            'nav-users',
            'chat-conversation-list',
            'chat-new-conversation',
            'chat-delete-conversation',
            'chat-rename-conversation',
            'chat-copy-message',
            'chat-copy-code',
            'chat-mobile-menu',
            'chat-send-message',
            'dashboard-chart',
            'admin-org-switcher',
            'users-table',
            'pages-index',
            'pages-create',
            'pages-edit-preview',
            'pages-edit-save',
            'pages-duplicate',
            'pages-delete',
            'settings-nav-domains',
            'nav-admin-panel',
            'settings-nav-admin-panel',
        ]);
    }

    private function registerSeoViewComposer(): void
    {
        View::composer('app', function ($view): void {
            try {
                $settings = resolve(SeoSettings::class);
                $seo = [
                    'meta_title' => $settings->meta_title ?: config('app.name'),
                    'meta_description' => $settings->meta_description ?? '',
                    'og_image' => $settings->og_image,
                    'app_url' => mb_rtrim(config('app.url'), '/'),
                ];
            } catch (Throwable) {
                $seo = [
                    'meta_title' => config('app.name'),
                    'meta_description' => '',
                    'og_image' => null,
                    'app_url' => mb_rtrim(config('app.url'), '/'),
                ];
            }

            $seo['current_url'] = request()->url();
            $view->with('seo', $seo);
        });
    }

    private function registerActivityLogTaps(): void
    {
        try {
            if (Schema::hasTable(config('activitylog.table_name', 'activity_log'))) {
                $activityModel = ActivitylogServiceProvider::determineActivityModel();
                $activityModel::observe(ActivityLogObserver::class);
            }
        } catch (Throwable) {
            // DB may be unavailable (e.g. docs:sync --check in pre-commit, CI without DB)
        }

        Role::observe(RoleActivityObserver::class);
        Permission::observe(PermissionActivityObserver::class);
    }

    /**
     * When impersonating, use the impersonator as activity log causer so the real actor is recorded.
     */
    private function registerActivityLogImpersonationCauser(): void
    {
        if (! class_exists(\STS\FilamentImpersonate\Facades\Impersonation::class)) {
            return;
        }

        $resolver = $this->app->make(ActivitylogCauserResolver::class);
        $resolver->resolveUsing(function (Model|int|string|null $subject): ?Model {
            if ($subject instanceof Model) {
                if (\STS\FilamentImpersonate\Facades\Impersonation::isImpersonating()) {
                    $current = $this->app->make(\Illuminate\Contracts\Auth\Factory::class)->guard(config('activitylog.default_auth_driver'))->user();
                    if ($current instanceof Model && $subject->is($current)) {
                        $impersonator = \STS\FilamentImpersonate\Facades\Impersonation::getImpersonator();

                        return $impersonator instanceof Model ? $impersonator : $subject;
                    }
                }

                return $subject;
            }

            if (is_int($subject) || is_string($subject)) {
                $driver = config('activitylog.default_auth_driver');
                $guard = $this->app->make(\Illuminate\Contracts\Auth\Factory::class)->guard($driver);
                $provider = $guard->getProvider();
                $model = $provider?->retrieveById($subject);

                return $model instanceof Model ? $model : null;
            }

            if (\STS\FilamentImpersonate\Facades\Impersonation::isImpersonating()) {
                $impersonator = \STS\FilamentImpersonate\Facades\Impersonation::getImpersonator();

                return $impersonator instanceof Model ? $impersonator : null;
            }

            return $this->app->make(\Illuminate\Contracts\Auth\Factory::class)->guard(config('activitylog.default_auth_driver'))->user();
        });
    }
}
