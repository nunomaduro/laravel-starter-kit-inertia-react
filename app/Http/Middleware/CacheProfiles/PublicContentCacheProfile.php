<?php

declare(strict_types=1);

namespace App\Http\Middleware\CacheProfiles;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Spatie\ResponseCache\CacheProfiles\BaseCacheProfile;
use Symfony\Component\HttpFoundation\Response;

final class PublicContentCacheProfile extends BaseCacheProfile
{
    public function shouldCacheRequest(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if (Auth::check()) {
            return false;
        }

        if ($request->is('admin/*') || $request->is('api/*') || $request->is('telescope/*') || $request->is('horizon/*') || $request->is('pulse/*')) {
            return false;
        }

        return ! $request->is('login', 'register', 'forgot-password', 'reset-password*', 'verify-email*', 'favicon.ico');
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $response->isSuccessful();
    }

    public function enabled(Request $request): bool
    {
        return config('responsecache.enabled', false);
    }

    public function useCacheNameSuffix(Request $request): string
    {
        return 'guest';
    }

    public function cacheRequestUntil(Request $request): DateTime
    {
        $lifetime = config('responsecache.cache_lifetime_in_seconds', 60 * 60 * 24 * 7);

        return Date::now()->addSeconds($lifetime);
    }
}
