<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SetupWizardSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * When installation is not finished, show a static 503 page with CLI install instructions.
 * Only runs in local/testing environments. Skips health and webhook routes.
 */
final class RedirectToInstallerIfNotSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), ['up', 'up.ready'], true)) {
            return $next($request);
        }

        if (str_starts_with($request->path(), 'webhooks/')) {
            return $next($request);
        }

        // Allow tests (or config) to bypass setup check
        if (config('settings.setup_completed')) {
            return $next($request);
        }

        try {
            $wizard = resolve(SetupWizardSettings::class);
            if ($wizard->setup_completed) {
                return $next($request);
            }
        } catch (Throwable) {
            // Settings/DB not available — show install instructions
        }

        return response($this->html(), 503);
    }

    private function html(): string
    {
        return <<<'HTML'
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Application Not Installed</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f3f4f6;
                    color: #1f2937;
                    padding: 1rem;
                }
                @media (prefers-color-scheme: dark) {
                    body { background: #111827; color: #f9fafb; }
                    .card { background: #1f2937; border-color: #374151; }
                    code { background: #374151; color: #e5e7eb; }
                    .note { color: #9ca3af; }
                }
                .card {
                    max-width: 480px;
                    width: 100%;
                    background: #fff;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 2.5rem;
                    text-align: center;
                }
                h1 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem; }
                p { font-size: 0.938rem; line-height: 1.6; margin-bottom: 1.25rem; }
                code {
                    display: block;
                    background: #f3f4f6;
                    color: #1f2937;
                    padding: 0.75rem 1rem;
                    border-radius: 8px;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                    font-size: 0.875rem;
                    margin-bottom: 1.25rem;
                    user-select: all;
                }
                .note { font-size: 0.813rem; color: #6b7280; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>Application Not Installed</h1>
                <p>Run the installer from your terminal to set up the application:</p>
                <code>php artisan app:install</code>
                <p class="note">After install completes, visit <strong>/admin</strong> to access the dashboard.</p>
            </div>
        </body>
        </html>
        HTML;
    }
}
