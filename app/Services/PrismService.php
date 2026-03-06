<?php

declare(strict_types=1);

namespace App\Services;

use Prism\Prism\Contracts\Schema;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\RawSchema;
use Prism\Prism\Structured\PendingRequest as StructuredPendingRequest;
use Prism\Prism\Text\PendingRequest;
use Prism\Prism\Text\Response as TextResponse;
use Prism\Relay\Facades\Relay;

/**
 * Service for interacting with Prism AI providers.
 *
 * This service provides a convenient wrapper around Prism for common AI operations.
 * It defaults to OpenRouter but can be configured to use any Prism provider.
 */
final readonly class PrismService
{
    /**
     * Get the default provider from configuration.
     */
    public function defaultProvider(): Provider
    {
        $providerName = config('prism.defaults.provider', 'openrouter');

        return Provider::from($providerName);
    }

    /**
     * Get the default model from configuration.
     */
    public function defaultModel(): string
    {
        return config('prism.defaults.model', 'deepseek/deepseek-r1-0528:free');
    }

    /**
     * Get the default model for a specific provider.
     */
    public function defaultModelForProvider(Provider $provider): string
    {
        $providerName = mb_strtolower($provider->value);

        return config('prism.defaults.models.'.$providerName, $this->defaultModel());
    }

    /**
     * Check if AI is available and configured.
     */
    public function isAvailable(?Provider $provider = null): bool
    {
        $providerToCheck = $provider ?? $this->defaultProvider();

        return match ($providerToCheck) {
            Provider::OpenRouter => ! empty(config('prism.providers.openrouter.api_key')),
            Provider::OpenAI => ! empty(config('prism.providers.openai.api_key')),
            Provider::Anthropic => ! empty(config('prism.providers.anthropic.api_key')),
            Provider::Ollama => true, // Ollama doesn't require API key
            Provider::Mistral => ! empty(config('prism.providers.mistral.api_key')),
            Provider::Groq => ! empty(config('prism.providers.groq.api_key')),
            Provider::XAI => ! empty(config('prism.providers.xai.api_key')),
            Provider::Gemini => ! empty(config('prism.providers.gemini.api_key')),
            Provider::DeepSeek => ! empty(config('prism.providers.deepseek.api_key')),
            default => false,
        };
    }

    /**
     * Create a text generation request using OpenRouter.
     *
     * @param  string  $model  The model to use (e.g., 'openai/gpt-4o-mini')
     */
    public function text(?string $model = null): PendingRequest
    {
        return Prism::text()->using($this->defaultProvider(), $model ?? $this->defaultModel());
    }

    /**
     * Generate text from a prompt using OpenRouter.
     *
     * @param  string  $prompt  The prompt to send
     * @param  string|null  $model  The model to use (defaults to config)
     */
    public function generate(string $prompt, ?string $model = null): TextResponse
    {
        return $this->text($model)
            ->withPrompt($prompt)
            ->asText();
    }

    /**
     * Create a structured output request.
     *
     * @param  string|null  $model  The model to use (defaults to config)
     */
    public function structured(?string $model = null): StructuredPendingRequest
    {
        return Prism::structured()->using($this->defaultProvider(), $model ?? $this->defaultModel());
    }

    /**
     * Generate structured output from a prompt.
     *
     * @param  string  $prompt  The prompt to send
     * @param  string|object|array<string, mixed>  $schema  The schema for structured output (object implementing Schema, or JSON schema array)
     * @param  string|null  $model  The model to use (defaults to config)
     */
    public function generateStructured(string $prompt, string|object|array $schema, ?string $model = null): mixed
    {
        $schemaInstance = match (true) {
            is_array($schema) => new RawSchema('schema', $schema),
            is_string($schema) => new RawSchema('schema', json_decode($schema, true) ?? []),
            default => $schema,
        };

        assert($schemaInstance instanceof Schema);

        return $this->structured($model)
            ->withPrompt($prompt)
            ->withSchema($schemaInstance)
            ->asStructured();
    }

    /**
     * Create a text request with MCP tools from Relay.
     *
     * @param  string|array<int, string>  $servers  MCP server name(s) to get tools from
     * @param  string|null  $model  The model to use (defaults to config)
     */
    public function withTools(string|array $servers, ?string $model = null): PendingRequest
    {
        $tools = is_array($servers)
            ? array_merge(...array_map(fn (string $server) => Relay::tools($server), $servers))
            : Relay::tools($servers);

        return $this->text($model)->withTools($tools);
    }

    /**
     * Generate text using a different provider.
     *
     * @param  Provider  $provider  The provider to use
     * @param  string  $model  The model to use
     */
    public function using(Provider $provider, string $model): PendingRequest
    {
        return Prism::text()->using($provider, $model);
    }
}
