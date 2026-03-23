<?php

declare(strict_types=1);

namespace App\Services;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Structured\PendingRequest as StructuredPendingRequest;
use Prism\Prism\Text\PendingRequest;
use Prism\Relay\Facades\Relay;

/**
 * Thin Prism/Relay bridge.
 *
 * NOTE: Direct Prism usage here is intentional — prism-php/relay has no laravel/ai equivalent.
 * All other AI functionality should use laravel/ai Agent classes directly.
 */
final readonly class PrismService
{
    public function isAvailable(?Provider $provider = null): bool
    {
        $check = $provider ?? Provider::from(config('prism.defaults.provider', 'openrouter'));

        return match ($check) {
            Provider::OpenRouter => ! empty(config('prism.providers.openrouter.api_key')),
            Provider::OpenAI => ! empty(config('prism.providers.openai.api_key')),
            Provider::Anthropic => ! empty(config('prism.providers.anthropic.api_key')),
            Provider::Mistral => ! empty(config('prism.providers.mistral.api_key')),
            Provider::Groq => ! empty(config('prism.providers.groq.api_key')),
            Provider::XAI => ! empty(config('prism.providers.xai.api_key')),
            Provider::Gemini => ! empty(config('prism.providers.gemini.api_key')),
            Provider::DeepSeek => ! empty(config('prism.providers.deepseek.api_key')),
            Provider::Ollama => true,
            default => false,
        };
    }

    /**
     * Create a Prism text request with the default (or specified) provider and model.
     */
    public function text(?string $model = null): PendingRequest
    {
        $provider = $this->defaultProvider();
        $resolvedModel = $model ?? $this->defaultModel();

        return Prism::text()->using($provider, $resolvedModel);
    }

    /**
     * Create a Prism structured request with the default provider and model.
     */
    public function structured(?string $model = null): StructuredPendingRequest
    {
        $provider = $this->defaultProvider();
        $resolvedModel = $model ?? $this->defaultModel();

        return Prism::structured()->using($provider, $resolvedModel);
    }

    /**
     * Create a Prism text request with a specific provider and model.
     */
    public function using(Provider $provider, string $model): PendingRequest
    {
        return Prism::text()->using($provider, $model);
    }

    /**
     * Get the default provider.
     */
    public function defaultProvider(): Provider
    {
        return Provider::from(config('prism.defaults.provider', 'openrouter'));
    }

    /**
     * Get the default model.
     */
    public function defaultModel(): string
    {
        return (string) config('prism.defaults.model', 'deepseek/deepseek-r1-0528:free');
    }

    /**
     * Get the default model for a given provider.
     */
    public function defaultModelForProvider(Provider $provider): string
    {
        $providerKey = $provider->value;

        return (string) config("prism.providers.{$providerKey}.model", $this->defaultModel());
    }

    /**
     * Create a Prism text request pre-loaded with tools from the given MCP server(s) via Relay.
     *
     * NOTE: prism-php/relay has no laravel/ai equivalent — this is the only intentional direct Prism usage.
     *
     * @param  string|array<int, string>  $servers  MCP server name(s)
     */
    public function withTools(string|array $servers, ?string $model = null): PendingRequest
    {
        $tools = is_array($servers)
            ? array_merge(...array_map(fn (string $s) => Relay::tools($s), $servers))
            : Relay::tools($servers);

        $provider = Provider::from(config('prism.defaults.provider', 'openrouter'));
        $resolvedModel = $model ?? config('prism.defaults.model', 'deepseek/deepseek-r1-0528:free');

        return Prism::text()->using($provider, $resolvedModel)->withTools($tools);
    }
}
