<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lightweight, opt-in telemetry for tracking product usage.
 *
 * Tracks events like module installs, AI queries, command usage.
 * Respects the telemetry_enabled setting — never records when disabled.
 * Data stays local (logged to telemetry channel) — no external services.
 */
final class TelemetryService
{
    /**
     * Track a telemetry event.
     *
     * @param  array<string, mixed>  $properties
     */
    public function track(string $event, array $properties = []): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        Log::channel('telemetry')->info($event, [
            'event' => $event,
            'properties' => $properties,
            'user_id' => auth()->id(),
            'organization_id' => $this->getCurrentOrganizationId(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track a module install event.
     */
    public function trackModuleInstall(string $moduleName): void
    {
        $this->track('module.installed', ['module' => $moduleName]);
    }

    /**
     * Track an AI query event.
     */
    public function trackAiQuery(string $type, ?int $tokensUsed = null): void
    {
        $this->track('ai.query', [
            'type' => $type,
            'tokens_used' => $tokensUsed,
        ]);
    }

    /**
     * Track a command execution.
     */
    public function trackCommand(string $command): void
    {
        $this->track('command.executed', ['command' => $command]);
    }

    /**
     * Track a showcase page visit.
     */
    public function trackShowcaseVisit(string $feature): void
    {
        $this->track('showcase.visited', ['feature' => $feature]);
    }

    public function isEnabled(): bool
    {
        return (bool) config('telemetry.enabled', false);
    }

    private function getCurrentOrganizationId(): ?int
    {
        try {
            return TenantContext::id();
        } catch (Throwable) {
            return null;
        }
    }
}
