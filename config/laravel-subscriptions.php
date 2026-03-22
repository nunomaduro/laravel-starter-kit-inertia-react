<?php

declare(strict_types=1);

use Laravelcm\Subscriptions\Models\Feature;
use Laravelcm\Subscriptions\Models\SubscriptionUsage;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Tables
    |--------------------------------------------------------------------------
    |
    |
    */

    'tables' => [
        'plans' => 'plans',
        'features' => 'plan_features',
        'subscriptions' => 'plan_subscriptions',
        'subscription_usage' => 'plan_subscription_usage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Models
    |--------------------------------------------------------------------------
    |
    | Models used to manage subscriptions. You can replace to use your own models,
    | but make sure that you have the same functionalities or that your models
    | extend from each model that you are going to replace.
    |
    */

    'models' => [
        'plan' => Plan::class,
        'feature' => Feature::class,
        'subscription' => Subscription::class,
        'subscription_usage' => SubscriptionUsage::class,
    ],

];
