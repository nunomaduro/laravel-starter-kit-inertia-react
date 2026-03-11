<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Layout Blade File
    |--------------------------------------------------------------------------
    |
    | This value is used to reference your main layout blade view to render
    | the views provided by this package. The layout view referenced here
    | should include Bootstrap 3 and FontAwesome 4 to work as intended.
    */
    'layout-view' => 'layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Layout Content Section Name
    |--------------------------------------------------------------------------
    |
    | Specify the name of the section in the view referenced above that is
    | used to render the main page content. If this does not match, you
    | will only get blank pages when accessing views in Governor.
    */
    'content-section' => 'content',

    /*
    |--------------------------------------------------------------------------
    | Authorization Model
    |--------------------------------------------------------------------------
    |
    | Here you can customize what model should be used for authorization checks
    | in the event that you have customized your authentication processes.
    */
    'auth-model-primary-key-type' => 'bigInteger',
    'models' => [
        'auth' => config('auth.providers.users.model', config('auth.model')),
        'action' => GeneaLabs\LaravelGovernor\Action::class,
        'assignment' => GeneaLabs\LaravelGovernor\Assignment::class,
        'entity' => GeneaLabs\LaravelGovernor\Entity::class,
        'group' => GeneaLabs\LaravelGovernor\Group::class,
        'ownership' => GeneaLabs\LaravelGovernor\Ownership::class,
        'permission' => GeneaLabs\LaravelGovernor\Permission::class,
        'role' => GeneaLabs\LaravelGovernor\Role::class,
        'team' => GeneaLabs\LaravelGovernor\Team::class,
        'invitation' => GeneaLabs\LaravelGovernor\TeamInvitation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model Name Property
    |--------------------------------------------------------------------------
    |
    | This value is used to display your users when assigning them to roles.
    | You can choose any property of your auth-model defined above that is
    | exposed via JSON.
    */
    'user-name-property' => 'name',

    /*
    |--------------------------------------------------------------------------
    | URL Prefix
    |--------------------------------------------------------------------------
    |
    | If you want to change the URL used by the browser to access the admin
    | pages, you can do so here. Be careful to avoid collisions with any
    | existing URLs of your app when doing so.
    */
    'url-prefix' => '/genealabs/laravel-governor/',

    /*
    |--------------------------------------------------------------------------
    | Default SuperAdmin User
    |--------------------------------------------------------------------------
    |
    | You may optionally specify a set of SuperAdmin and Admin users that will
    | be created if they don't already exist, formatted as JSON.
    | Example: [{"name":"Joe Doe","email":"joe@example.com","password":"secret1"},{"name":"Jane Doe","email":"jane@example.com","password":"shhhhh1"}]
    */
    'superadmins' => env('GOVERNOR_SUPERADMINS'),
    'admins' => env('GOVERNOR_ADMINS'),

];
