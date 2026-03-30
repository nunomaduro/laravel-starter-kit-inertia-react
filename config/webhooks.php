<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Event Registry
    |--------------------------------------------------------------------------
    |
    | Events are grouped by domain. Top-level keys are group names used by
    | the grouped multi-select in the UI. Modules extend in their
    | ServiceProvider boot(): config(['webhooks.events.Contacts' => [...]])
    |
    */
    'events' => [
        'Users' => [
            'user.created' => 'A new user registered',
            'user.deleted' => 'A user was deleted',
        ],
        'Organizations' => [
            'organization.updated' => 'Organization settings changed',
        ],
        'Invitations' => [
            'invitation.sent' => 'An invitation was sent',
            'invitation.accepted' => 'An invitation was accepted',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Ping Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum seconds to wait when an org admin clicks "Test" on an endpoint.
    |
    */
    'timeout' => 5,

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker (Fuse) Settings
    |--------------------------------------------------------------------------
    |
    | Controls when endpoints are automatically protected from cascading
    | failures. See harris21/laravel-fuse documentation for details.
    |
    */
    'fuse' => [
        'threshold' => 50,    // Failure rate percentage to trip circuit
        'timeout' => 3600,    // Seconds before half-open probe (1 hour)
        'min_requests' => 3,  // Minimum requests before evaluating failure rate
    ],
];
