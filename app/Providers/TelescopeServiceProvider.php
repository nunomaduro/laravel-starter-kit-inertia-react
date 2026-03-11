<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

final class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal): bool {
            // Skip recording for installer so Telescope never touches DB before app is installed
            if ($entry->type === EntryType::REQUEST && isset($entry->content['uri'])) {
                $path = parse_url($entry->content['uri'], PHP_URL_PATH);
                if ($path !== null && str_starts_with((string) $path, '/install')) {
                    return false;
                }
            }

            return $isLocal
                || $entry->isReportableException()
                || $entry->isFailedRequest()
                || $entry->isFailedJob()
                || $entry->isScheduledTask()
                || $entry->isSlowQuery()
                || $entry->hasMonitoredTag();
        });

        Telescope::tag(function (IncomingEntry $entry): array {
            if ($entry->type === EntryType::REQUEST && isset($entry->content['response_status'])) {
                return ['status:'.$entry->content['response_status']];
            }

            return [];
        });

        Telescope::avatar(function (?string $id, ?string $email): ?string {
            if ($id === null) {
                return null;
            }

            $user = User::query()->find($id);

            return $user?->avatar;
        });
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', fn (?User $user = null): bool => $user instanceof User && $user->can('access admin panel'));
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    private function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }
}
