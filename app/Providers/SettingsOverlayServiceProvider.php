<?php

declare(strict_types=1);

namespace App\Providers;

use App\Settings\PrismSettings;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Writes Spatie settings values into config() at boot,
 * so all config() consumers transparently read DB-backed values.
 *
 * The overlay map is defined in config/settings-overlay.php, organized
 * by domain for navigability. Each entry maps setting properties to
 * config keys and declares whether per-org overrides are allowed.
 */
final class SettingsOverlayServiceProvider extends ServiceProvider
{
    /**
     * Cross-map PrismSettings API keys → Laravel AI SDK config keys.
     *
     * PrismSettings already overlays to prism.providers.*.api_key via the overlay map.
     * This copies those same keys into ai.providers.*.key so both SDKs share one source of truth.
     */
    private const array AI_KEY_CROSS_MAP = [
        'openai_api_key' => 'ai.providers.openai.key',
        'anthropic_api_key' => 'ai.providers.anthropic.key',
        'groq_api_key' => 'ai.providers.groq.key',
        'xai_api_key' => 'ai.providers.xai.key',
        'gemini_api_key' => 'ai.providers.gemini.key',
        'elevenlabs_api_key' => 'ai.providers.eleven.key',
        'openrouter_api_key' => 'ai.providers.openrouter.key',
    ];

    /**
     * Kept as a read-through accessor so existing code referencing
     * SettingsOverlayServiceProvider::OVERLAY_MAP continues to work.
     *
     * @return array<class-string<\Spatie\LaravelSettings\Settings>, array{map: array<string, string>, orgOverridable: bool}>
     */
    public static function overlayMap(): array
    {
        return config('settings-overlay', []);
    }

    /**
     * Write all Spatie settings values into config().
     * Can be called from tests to re-apply after changing settings.
     */
    public static function applyOverlay(): void
    {
        foreach (self::overlayMap() as $settingsClass => $config) {
            try {
                /** @var \Spatie\LaravelSettings\Settings $settings */
                $settings = resolve($settingsClass);

                foreach ($config['map'] as $property => $configKey) {
                    $value = $settings->{$property};
                    // API keys: only overlay when DB has a value, so .env remains the fallback
                    if (
                        (str_ends_with($configKey, 'api_key') || str_ends_with($configKey, '.key'))
                        && ($value === null || $value === '')
                    ) {
                        continue;
                    }
                    config()->set($configKey, $value);
                }
            } catch (Throwable) {
                // Settings table may not exist yet (fresh install, migrations pending).
                // Skip this group silently — config defaults from env remain in effect.
                continue;
            }
        }

        // Avoid instantiating Reverb with null credentials (e.g. package:discover, composer install)
        if (config('broadcasting.default') === 'reverb') {
            if (blank(config('broadcasting.connections.reverb.key')) || blank(config('broadcasting.connections.reverb.app_id'))) {
                config()->set('broadcasting.default', 'log');
            }
        }

        // Recompute OAuth redirect URLs from the DB-backed app.url so they are always correct
        // regardless of whether APP_URL is set in .env.
        $appUrl = mb_rtrim((string) config('app.url', 'http://localhost'), '/');
        config()->set('services.google.redirect', $appUrl.'/auth/google/callback');
        config()->set('services.github.redirect', $appUrl.'/auth/github/callback');

        // Cross-map PrismSettings API keys to Laravel AI SDK config (only when DB has a value)
        try {
            $prism = resolve(PrismSettings::class);

            foreach (self::AI_KEY_CROSS_MAP as $property => $configKey) {
                $value = $prism->{$property};
                if ($value === null || $value === '') {
                    continue;
                }
                config()->set($configKey, $value);
            }
        } catch (Throwable) {
            // PrismSettings not available yet — skip silently.
        }
    }

    /**
     * Get all config keys that are overridable per-organization.
     *
     * @return array<string, string> Map of "group.property" => "config.key"
     */
    public static function orgOverridableKeys(): array
    {
        $keys = [];

        foreach (self::overlayMap() as $settingsClass => $config) {
            if (! $config['orgOverridable']) {
                continue;
            }

            $group = $settingsClass::group();

            foreach ($config['map'] as $property => $configKey) {
                $keys[sprintf('%s.%s', $group, $property)] = $configKey;
            }
        }

        return $keys;
    }

    public function boot(): void
    {
        self::applyOverlay();
    }
}
