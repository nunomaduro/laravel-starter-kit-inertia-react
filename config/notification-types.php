<?php

declare(strict_types=1);

return [
    'invoice_paid' => [
        'label' => 'Invoice paid',
        'channels' => ['database', 'email'],
    ],
    'org_invitation' => [
        'label' => 'Organization invitation',
        'channels' => ['database', 'email'],
    ],
    'member_added' => [
        'label' => 'New member joined',
        'channels' => ['database'],
    ],
    'member_removed' => [
        'label' => 'Member removed',
        'channels' => ['database'],
    ],
];
