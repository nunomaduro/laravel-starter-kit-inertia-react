---
title: Prevent Security Misconfiguration
impact: HIGH
impactDescription: Prevents information disclosure, header-based attacks, and environment exposure
tags: security, misconfiguration, headers, cors, env, csp, owasp-a05
---

## Prevent Security Misconfiguration

**Impact: HIGH (Prevents information disclosure, header-based attacks, and environment exposure)**

## Why It Matters

- **Risk**: Misconfigured environments expose stack traces, skip security headers, or allow any origin to call your API
- **Impact**: Attacker reads full stack traces (file paths, DB credentials hinted), clickjacks the app, or calls authenticated API endpoints from any domain
- **OWASP**: A05:2021 — Security Misconfiguration

## Incorrect — Debug Mode in Production

```php
// ❌ APP_DEBUG=true in production
// Any exception exposes: file paths, environment variables, stack trace, DB config hints
APP_DEBUG=true
APP_ENV=production  // Contradiction — debug should be false in production
```

```php
<?php

// What a user sees when APP_DEBUG=true throws an exception:
// Illuminate\Database\QueryException: SQLSTATE[42S02]
// Connection to /var/www/app/.env database failed
// Stack trace shows every internal file path
```

## Correct — Environment Configuration

```php
# ✅ .env — production values
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-unique-64-char-key-here

# Database — restricted user, not root
DB_USERNAME=aittendance_user  # Not root
DB_PASSWORD=strong-random-password

# Session — secure for HTTPS
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=30
```

## Incorrect — Missing Security Headers

```php
<?php

// ❌ No security headers middleware — browser has no protection instructions
// App is vulnerable to:
// - Clickjacking (no X-Frame-Options)
// - MIME sniffing (no X-Content-Type-Options)
// - Inline script XSS (no Content-Security-Policy)
// - Protocol downgrade (no Strict-Transport-Security)
```

## Correct — Security Headers Middleware

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generate CSP nonce — used by Vite and @routes directive
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        // Set headers not handled by CDN/reverse proxy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",   // Nonce eliminates unsafe-inline
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]));

        // Only set in non-production — Cloudflare/CDN handles these in production
        if (! app()->environment('production')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
```

```php
<?php

// ✅ Register SecurityHeaders in web middleware group
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        App\Http\Middleware\HandleInertiaRequests::class,
        App\Http\Middleware\SecurityHeaders::class,
    ]);
})
```

```blade
{{-- ✅ Pass nonce to @routes so Ziggy script passes CSP --}}
@routes(nonce: Vite::cspNonce())
@viteReactRefresh
@vite(['resources/css/app.css', 'resources/js/app.tsx'])
```

## Incorrect — CORS Misconfiguration

```php
<?php

// ❌ Wildcard allowed origins — any website can call your authenticated API
return [
    'allowed_origins' => ['*'],  // DANGEROUS for authenticated routes
    'allowed_methods' => ['*'],
];
```

## Correct — CORS Configuration

```php
<?php

// ✅ config/cors.php — restrict to your domain
return [
    'paths'               => ['api/*'],
    'allowed_origins'     => ['https://yourdomain.com'],  // Explicit domain only
    'allowed_methods'     => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_headers'     => ['Content-Type', 'X-Requested-With', 'Authorization'],
    'supports_credentials' => true,
];
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `APP_DEBUG=false` | Production environment always |
| `Vite::useCspNonce()` | Eliminate `unsafe-inline` from script-src |
| `SecurityHeaders` middleware | Apply all security headers in web group |
| `allowed_origins: ['https://...']` | CORS — never use `*` for authenticated routes |
| CDN handles HSTS, X-Frame, X-Content-Type | Avoid duplicate headers when behind Cloudflare |

Reference: [OWASP Secure Headers](https://owasp.org/www-project-secure-headers/) | [MDN Content-Security-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
