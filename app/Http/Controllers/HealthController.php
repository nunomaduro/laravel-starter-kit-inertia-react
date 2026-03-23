<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class HealthController
{
    /**
     * Liveness: app and database only. Use for Kubernetes liveness probe or simple load balancer health.
     */
    public function up(): JsonResponse
    {
        $checks = ['app' => true];
        try {
            DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (Throwable) {
            $checks['database'] = false;
        }

        $ok = ! in_array(false, $checks, true);

        return response()->json(['status' => $ok ? 'ok' : 'degraded', 'checks' => $checks], $ok ? 200 : 503);
    }

    /**
     * Readiness: app, database, cache, and optionally queue. Use for Kubernetes readiness probe.
     */
    public function ready(): JsonResponse
    {
        $checks = ['app' => true];

        try {
            DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (Throwable) {
            $checks['database'] = false;
        }

        try {
            $key = 'health_ready_'.Str::random(8);
            Cache::store()->put($key, true, 5);
            $checks['cache'] = Cache::store()->get($key) === true;
            Cache::store()->forget($key);
        } catch (Throwable) {
            $checks['cache'] = false;
        }

        $ok = ! in_array(false, $checks, true);

        return response()->json(['status' => $ok ? 'ok' : 'degraded', 'checks' => $checks], $ok ? 200 : 503);
    }
}
