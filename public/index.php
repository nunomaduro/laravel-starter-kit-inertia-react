<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Auto-generate APP_KEY for web installer in local env (before Laravel boots).
$requestPath = mb_rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
if ($requestPath === '/install') {
    (static function (string $basePath): void {
        $envPath = $basePath.'/.env';
        if (! is_file($envPath) || ! is_readable($envPath) || ! is_writable($envPath)) {
            return;
        }
        $content = (string) file_get_contents($envPath);
        $appEnv = null;
        $hasAppKey = false;
        $appKeyEmpty = true;
        foreach (explode("\n", $content) as $line) {
            $line = mb_trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_starts_with($line, 'APP_ENV=')) {
                $appEnv = mb_trim(mb_trim(mb_substr($line, 8)), '"\'');
            }
            if (str_starts_with($line, 'APP_KEY=')) {
                $hasAppKey = true;
                $val = mb_trim(mb_trim(mb_substr($line, 8)), '"\'');
                if ($val !== '') {
                    $appKeyEmpty = false;
                }
            }
        }
        // Only run in local (or when APP_ENV not set, e.g. fresh .env)
        $env = $appEnv ?? 'local';
        if (mb_strtolower($env) !== 'local') {
            return;
        }
        if (! $appKeyEmpty) {
            return;
        }
        $key = 'base64:'.base64_encode(random_bytes(32));
        if ($hasAppKey) {
            $newContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $content);
        } else {
            $newContent = $content.("\nAPP_KEY=".$key."\n");
        }
        if ($newContent !== $content) {
            file_put_contents($envPath, $newContent);
        }
    })(__DIR__.'/..');
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
