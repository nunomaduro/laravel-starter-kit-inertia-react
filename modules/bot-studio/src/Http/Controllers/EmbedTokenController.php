<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentEmbedToken;
use Modules\BotStudio\Services\EmbedTokenService;

final readonly class EmbedTokenController
{
    public function __construct(
        private EmbedTokenService $tokenService,
    ) {}

    /**
     * Create a new embed token for the agent.
     */
    public function store(Request $request, AgentDefinition $agentDefinition): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'allowed_domains' => ['nullable', 'array'],
            'allowed_domains.*' => ['string', 'max:255'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $result = $this->tokenService->create(
            $agentDefinition,
            $validated['name'],
            $validated['allowed_domains'] ?? [],
        );

        // Update rate limit if provided
        if (isset($validated['rate_limit_per_minute'])) {
            $result['model']->update(['rate_limit_per_minute' => $validated['rate_limit_per_minute']]);
        }

        return response()->json([
            'token' => $result['model'],
            'plain_token' => $result['plainToken'],
        ], 201);
    }

    /**
     * Update an existing embed token.
     */
    public function update(Request $request, AgentDefinition $agentDefinition, AgentEmbedToken $embedToken): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'allowed_domains' => ['nullable', 'array'],
            'allowed_domains.*' => ['string', 'max:255'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $embedToken->update($validated);

        return response()->json(['token' => $embedToken->fresh()]);
    }

    /**
     * Delete an embed token.
     */
    public function destroy(AgentDefinition $agentDefinition, AgentEmbedToken $embedToken): JsonResponse
    {
        $embedToken->delete();

        return response()->json(null, 204);
    }
}
