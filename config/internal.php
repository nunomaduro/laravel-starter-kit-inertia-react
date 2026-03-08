<?php

declare(strict_types=1);

return [
    'allowed_ips' => array_filter(
        array_map(trim(...), explode(',', (string) env('INTERNAL_ALLOWED_IPS', '127.0.0.1,::1')))
    ),
];
