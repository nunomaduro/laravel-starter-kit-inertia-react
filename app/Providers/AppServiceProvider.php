<?php

declare(strict_types=1);

namespace App\Providers;

use App\Ai\Tools\SemanticSearchTool;
use App\Events\User\UserCreated;
use App\Listeners\CreatePersonalOrganizationOnUserCreated;
use App\Listeners\LogImpersonationEvents;
use App\Listeners\MigrationListener;
use App\Listeners\RecordWebhookFailure;
use App\Listeners\RecordWebhookSuccess;
use App\Listeners\ScheduleOnboardingReminderOnUserCreated;
use App\Listeners\SendSlackAlertOnJobFailed;
use App\Models\Shareable;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use App\Observers\PermissionActivityObserver;
use App\Observers\RoleActivityObserver;
use App\Observers\UserObserver;
use App\Policies\ShareablePolicy;
use App\Services\PrismService;
use App\Settings\AuthSettings;
use App\Settings\SeoSettings;
use App\Support\ModuleLoader;
use App\Support\ModuleToolRegistry;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\CauserResolver as ActivitylogCauserResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;
use STS\FilamentImpersonate\Events\EnterImpersonation;
use STS\FilamentImpersonate\Events\LeaveImpersonation;
use Throwable;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Alias for machour/laravel-data-table: package may resolve ColumnBuilder to root namespace.
        // Guard: only alias if the short name is not already defined (avoids redeclare in paratest/workers).
        if (
            class_exists(\Machour\DataTable\Columns\ColumnBuilder::class)
            && ! class_exists(\Machour\DataTable\ColumnBuilder::class, false)
        ) {
            class_alias(\Machour\DataTable\Columns\ColumnBuilder::class, \Machour\DataTable\ColumnBuilder::class);
        }

        $this->app->singleton(PrismService::class, fn (): PrismService => new PrismService);
        $this->app->singleton(ModuleToolRegistry::class);

        if (class_exists(\Essa\APIToolKit\Exceptions\Handler::class)) {
            $this->app->singleton(ExceptionHandler::class, \Essa\APIToolKit\Exceptions\Handler::class);
        }

        config(['filament-impersonate.redirect_to' => '/dashboard']);

        foreach (ModuleLoader::providers() as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot(): void
    {
        $this->bootStrictDefaults();

        // Disable Governor's ParseCustomPolicyActions middleware — incompatible
        // with Laravel 13 (__PHP_Incomplete_Class from cache deserialization).
        $this->app->bind(
            \GeneaLabs\LaravelGovernor\Http\Middleware\ParseCustomPolicyActions::class,
            fn (): object => new class
            {
                /** @param  Closure  $next */
                public function handle(mixed $request, mixed $next): mixed
                {
                    return $next($request);
                }
            }
        );

        $this->ensureSqliteDatabaseExists();
        $this->runMigrationsIfNeededForInstaller();

        $this->configurePasswordDefaults();

        $this->registerSeoViewComposer();
        $this->registerActivityLogTaps();
        $this->registerActivityLogImpersonationCauser();

        Gate::policy(Shareable::class, ShareablePolicy::class);

        Gate::define('viewPulse', fn (?User $user = null): bool => $user instanceof User && $user->can('access admin panel'));

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
        Event::listen(UserCreated::class, CreatePersonalOrganizationOnUserCreated::class);
        Event::listen(UserCreated::class, ScheduleOnboardingReminderOnUserCreated::class);
        Event::listen(WebhookCallSucceededEvent::class, RecordWebhookSuccess::class);
        Event::listen(WebhookCallFailedEvent::class, RecordWebhookFailure::class);

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event): void {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
            $event->extendSocialite('github', \SocialiteProviders\GitHub\Provider::class);
        });

        User::observe(UserObserver::class);

        $this->app->make(ModuleToolRegistry::class)->registerBaseTool(SemanticSearchTool::class);
    }

    private function userHasBypassPermissions(object $user): bool
    {
        // Use isSuperAdmin() which does a direct DB query with organization_id=0,
        // bypassing Spatie's team-scoped hasRole() that fails when a tenant is active.
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

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

    private function registerSeoViewComposer(): void
    {
        View::composer('app', function ($view): void {
            $settings = rescue(fn () => resolve(SeoSettings::class));

            $seo = [
                'meta_title' => $settings?->meta_title ?: config('app.name'),
                'meta_description' => $settings?->meta_description ?? '',
                'og_image' => $settings?->og_image,
                'app_url' => mb_rtrim(config('app.url'), '/'),
                'current_url' => request()->url(),
            ];

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

    /**
     * Create the SQLite database file if the default connection is SQLite and the file does not exist.
     * Allows the app (and installer) to boot when .env has no DB_* and Laravel falls back to sqlite.
     */
    private function bootStrictDefaults(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::automaticallyEagerLoadRelationships();
        Date::use(CarbonImmutable::class);
        DB::prohibitDestructiveCommands($this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceHttps();
        }
    }

    private function ensureSqliteDatabaseExists(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $path = config('database.connections.sqlite.database');
        if ($path === null || $path === ':memory:') {
            return;
        }

        if (! file_exists($path)) {
            touch($path);
        }
    }

    /**
     * Run migrations when in local with a fresh SQLite DB so the installer can load (middleware needs cache table).
     */
    private function runMigrationsIfNeededForInstaller(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        if (config('database.default') !== 'sqlite') {
            return;
        }

        try {
            if (Schema::hasTable('migrations')) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        Artisan::call('migrate', ['--force' => true]);
    }
}
