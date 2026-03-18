<?php

declare(strict_types=1);

/*
 * Module toggle map.
 *
 * Each key is a module directory name under modules/ and maps to a boolean
 * indicating whether that module is enabled. Only the installer, module:enable,
 * and module:disable commands should write this file.
 *
 * No env() calls — this file must be safe to cache via config:cache.
 */

return [
    'blog' => true,
    'changelog' => true,
    'help' => true,
    'contact' => true,
    'announcements' => true,
    'gamification' => true,
    'reports' => true,
    'dashboards' => true,
];
