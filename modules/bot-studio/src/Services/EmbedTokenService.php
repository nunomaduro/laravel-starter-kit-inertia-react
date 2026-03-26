<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

use Illuminate\Support\Str;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentEmbedToken;

final class EmbedTokenService
{
    /**
     * Create a new embed token for an agent definition.
     *
     * @param  array<int, string>  $domains
     * @return array{model: AgentEmbedToken, plainToken: string}
     */
    public function create(AgentDefinition $definition, string $name, array $domains = []): array
    {
        $plainToken = Str::random(48);

        $model = $definition->embedTokens()->create([
            'organization_id' => $definition->organization_id,
            'token' => hash('sha256', $plainToken),
            'name' => $name,
            'allowed_domains' => $domains,
        ]);

        return ['model' => $model, 'plainToken' => $plainToken];
    }

    /**
     * Verify a plain token and return the model if valid.
     */
    public function verify(string $plainToken): ?AgentEmbedToken
    {
        $hash = hash('sha256', $plainToken);

        /** @var AgentEmbedToken|null $token */
        $token = AgentEmbedToken::query()
            ->where('token', $hash)
            ->where('is_active', true)
            ->first();

        return $token;
    }

    /**
     * Validate that an origin is allowed by the token's domain list.
     *
     * An empty allowed_domains list permits all origins.
     */
    public function validateDomain(AgentEmbedToken $token, ?string $origin): bool
    {
        $domains = $token->allowed_domains;

        if ($domains === [] || $domains === null) {
            return true;
        }

        if ($origin === null || $origin === '') {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        if ($host === null || $host === false) {
            return false;
        }

        foreach ($domains as $domain) {
            if (strcasecmp($host, $domain) === 0) {
                return true;
            }

            // Support wildcard subdomains (e.g., *.example.com)
            if (str_starts_with($domain, '*.') && str_ends_with(mb_strtolower($host), mb_strtolower(mb_substr($domain, 1)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record a usage event: increment request count and update last_used_at.
     */
    public function recordUsage(AgentEmbedToken $token): void
    {
        $token->increment('request_count');
        $token->update(['last_used_at' => now()]);
    }
}
