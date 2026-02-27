<?php

declare(strict_types=1);

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         */

        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */

        'role' => Spatie\Permission\Models\Role::class,

    ],

    'table_names' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'roles' => 'roles',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'permissions' => 'permissions',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your models permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_permissions' => 'model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_roles' => 'model_has_roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        /*
         * Change this if you want to name the related pivots other than defaults
         */
        'role_pivot_key' => null, // default 'role_id',
        'permission_pivot_key' => null, // default 'permission_id',

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         */

        'model_morph_key' => 'model_id',

        /*
         * Change this if you want to use the teams feature and your related model's
         * foreign key is other than `team_id`. We use organization_id for multi-tenancy.
         */

        'team_foreign_key' => 'organization_id',
    ],

    /*
     * When set to true, the method for checking permissions will be registered on the gate.
     * Set this to false if you want to implement custom logic for checking permissions.
     */

    'register_permission_check_method' => true,

    /*
     * When set to true, Laravel\Octane\Events\OperationTerminated event listener will be registered
     * this will refresh permissions on every TickTerminated, TaskTerminated and RequestTerminated
     * NOTE: This should not be needed in most cases, but an Octane/Vapor combination benefited from it.
     */
    'register_octane_reset_listener' => false,

    /*
     * Events will fire when a role or permission is assigned/unassigned:
     * \Spatie\Permission\Events\RoleAttachedEvent
     * \Spatie\Permission\Events\RoleDetachedEvent
     * \Spatie\Permission\Events\PermissionAttachedEvent
     * \Spatie\Permission\Events\PermissionDetachedEvent
     *
     * To enable, set to true, and then create listeners to watch these events.
     */
    'events_enabled' => false,

    /*
     * Teams Feature.
     * When set to true the package implements teams using the 'team_foreign_key'.
     * If you want the migrations to register the 'team_foreign_key', you must
     * set this to true before doing the migration.
     * If you already did the migration then you must make a new migration to also
     * add 'team_foreign_key' to 'roles', 'model_has_roles', and 'model_has_permissions'
     * (view the latest version of this package's migration file)
     */

    'teams' => true,

    /*
     * The class to use to resolve the permissions team id (current organization).
     */
    'team_resolver' => App\Services\OrganizationTeamResolver::class,

    /*
     * Passport Client Credentials Grant
     * When set to true the package will use Passports Client to check permissions
     */

    'use_passport_client_credentials' => false,

    /*
     * When set to true, the required permission names are added to exception messages.
     * This could be considered an information leak in some contexts, so the default
     * setting is false here for optimum safety.
     */

    'display_permission_in_exception' => false,

    /*
     * When set to true, the required role names are added to exception messages.
     * This could be considered an information leak in some contexts, so the default
     * setting is false here for optimum safety.
     */

    'display_role_in_exception' => false,

    /*
     * By default wildcard permission lookups are disabled.
     * See documentation to understand supported syntax.
     */

    'enable_wildcard_permission' => false,

    /*
     * Route-based permissions (dynamic RBAC)
     *
     * When route_based_enforcement is true, AutoPermissionMiddleware runs on web routes
     * and requires the user to have a permission matching the route name for named
     * application routes that are not in skip_patterns and do not already have
     * explicit permission/role middleware.
     */
    'route_based_enforcement' => env('PERMISSION_ROUTE_BASED_ENFORCEMENT', false),

    /*
     * Require all application routes to have a name (for permission:sync-routes and CI).
     * When true, permission:check-routes fails if any app route is unnamed.
     */
    'require_named_routes' => env('PERMISSION_REQUIRE_NAMED_ROUTES', true),

    /*
     * Default role assigned to newly registered or created users when no role is set.
     * Set to null to skip automatic assignment. The role must exist (e.g. seeded).
     */
    'default_role' => env('PERMISSION_DEFAULT_ROLE', 'user'),

    /*
     * Permission names to assign to the default role when seeding. Empty = no permissions
     * (authenticated routes in route_skip_patterns do not require a permission).
     * Set to e.g. ['dashboard'] to give the default role baseline permissions and satisfy permission:health.
     */
    'default_role_permissions' => [],

    /**
     * Route name patterns to skip for automatic permission checking.
     * Use '*' for wildcard (e.g. 'password.*' matches password.request, password.reset).
     */
    'route_skip_patterns' => [
        'api',
        'login',
        'login.store',
        'logout',
        'register',
        'register.store',
        'password.*',
        'verification.*',
        'home',
        'favicon',
        'dashboard',
        'up',
        'settings',
        'user-profile.edit',
        'user-profile.update',
        'password.edit',
        'password.update',
        'appearance.edit',
        'two-factor.*',
        'user.destroy',
        'filament.*',
        'storage.*',
        'boost.*',
        'scramble.*',
    ],

    /*
     * Permission categories (wildcard grouping for role assignment)
     *
     * Optional. When permission_categories is set, RolesAndPermissionsSeeder can assign
     * permissions to roles by pattern (e.g. "users.*") instead of listing each permission.
     * See config/permission_categories.php and docs/developer/backend/permissions.md.
     */
    'permission_categories_enabled' => env('PERMISSION_CATEGORIES_ENABLED', false),

    /* Cache-specific settings */

    'cache' => [

        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         */

        'expiration_time' => DateInterval::createFromDateString('24 hours'),

        /*
         * The cache key used to store all permissions.
         */

        'key' => 'spatie.permission.cache',

        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */

        'store' => 'default',
    ],
];
