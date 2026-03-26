<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\ReasoningStart;
use Laravel\Ai\Streaming\Events\StreamEnd;
use Laravel\Ai\Streaming\Events\StreamStart;
use Laravel\Ai\Streaming\Events\TextDelta;
use Modules\BotStudio\Ai\Tools\KnowledgeSearchTool;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentEmbedToken;
use Modules\BotStudio\Services\EmbedTokenService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final readonly class EmbedApiController
{
    public function __construct(
        private EmbedTokenService $tokenService,
    ) {}

    /**
     * Return agent configuration for the embed widget.
     */
    public function config(string $token): JsonResponse
    {
        $embedToken = $this->tokenService->verify($token);

        if ($embedToken === null) {
            return response()->json(['message' => 'Invalid or inactive token.'], 401);
        }

        $definition = $embedToken->agentDefinition;

        if (! $definition->embed_enabled) {
            return response()->json(['message' => 'Embedding is not enabled for this agent.'], 403);
        }

        $theme = $definition->embed_theme ?? [];

        $response = response()->json([
            'agent' => [
                'name' => $definition->name,
                'slug' => $definition->slug,
                'avatar_url' => $definition->avatar_path,
                'description' => $definition->description,
            ],
            'theme' => [
                'primary_color' => $theme['primary_color'] ?? '#0d9488',
                'position' => $theme['position'] ?? 'bottom-right',
                'greeting' => $theme['greeting'] ?? 'Hi! How can I help you?',
                'placeholder' => $theme['placeholder'] ?? 'Type a message...',
                'show_powered_by' => $theme['show_powered_by'] ?? true,
            ],
            'conversation_starters' => $definition->conversation_starters ?? [],
        ]);

        return $this->withCorsHeaders($response, $embedToken);
    }

    /**
     * Handle a chat message from the embed widget, streaming NDJSON.
     */
    public function chat(string $token, Request $request): StreamedResponse|JsonResponse
    {
        $embedToken = $this->tokenService->verify($token);

        if ($embedToken === null) {
            return response()->json(['message' => 'Invalid or inactive token.'], 401);
        }

        $definition = $embedToken->agentDefinition;

        if (! $definition->embed_enabled) {
            return response()->json(['message' => 'Embedding is not enabled for this agent.'], 403);
        }

        // Validate origin domain
        $origin = $request->header('Origin') ?? $request->header('Referer');

        if (! $this->tokenService->validateDomain($embedToken, $origin)) {
            return response()->json(['message' => 'Domain not allowed.'], 403);
        }

        // Rate limiting
        $rateLimitKey = sprintf('embed:%s', $embedToken->id);
        $maxAttempts = $embedToken->rate_limit_per_minute ?: 30;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            return response()->json(['message' => 'Rate limit exceeded. Please try again later.'], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        // Extract prompt
        $prompt = $this->extractPrompt($request);

        if ($prompt === '') {
            return response()->json([
                'message' => 'A message is required.',
                'errors' => ['message' => ['The message field is required.']],
            ], 422);
        }

        // Record usage
        $this->tokenService->recordUsage($embedToken);

        // Build agent with ONLY KnowledgeSearchTool (no org tools)
        $organization = $embedToken->organization;
        $definition->load('knowledgeFiles');

        /** @var array<int, int> $indexedFileIds */
        $indexedFileIds = $definition->knowledgeFiles
            ->where('status', 'indexed')
            ->pluck('id')
            ->all();

        $tools = [];

        if ($indexedFileIds !== []) {
            $tools[] = new KnowledgeSearchTool(
                $indexedFileIds,
                $organization->id,
            );
        }

        // We need a user for the AI SDK — use (or create) a system embed user
        $user = $this->getOrCreateEmbedUser($organization);

        try {
            $agent = new \App\Ai\Agents\OrgScopedAgent(
                $organization,
                $user,
                app(\App\Support\ModuleToolRegistry::class),
            );

            $agent->withCustomPrompt($this->resolvePromptVariables($definition, $organization, $user));
            $agent->withCustomTools($tools);

            $stream = $agent->stream($prompt);
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => 'AI request failed: '.$throwable->getMessage(),
            ], 502);
        }

        $runId = null;
        $messageId = null;
        $contentAccumulator = '';

        $streamedResponse = response()->stream(
            function () use ($stream, &$runId, &$messageId, &$contentAccumulator): void {
                if (ob_get_level() !== 0) {
                    ob_end_clean();
                }

                try {
                    foreach ($stream as $event) {
                        if (connection_aborted() !== 0) {
                            return;
                        }

                        $ts = (int) (microtime(true) * 1000);

                        if ($event instanceof StreamStart) {
                            $runId = $event->id;
                            $this->emit(['type' => 'RUN_STARTED', 'timestamp' => $ts, 'runId' => $runId]);

                            continue;
                        }

                        if ($event instanceof ReasoningStart || $event instanceof ReasoningDelta) {
                            if ($messageId === null) {
                                $messageId = $runId ?? ($event instanceof ReasoningStart ? $event->reasoningId : $event->reasoningId);
                                $this->emit(['type' => 'TEXT_MESSAGE_START', 'timestamp' => $ts, 'messageId' => $messageId, 'role' => 'assistant']);
                            }

                            if ($event instanceof ReasoningDelta) {
                                $contentAccumulator .= $event->delta;
                                $this->emit(['type' => 'TEXT_MESSAGE_CONTENT', 'timestamp' => $ts, 'messageId' => $messageId, 'delta' => $event->delta, 'content' => $contentAccumulator]);
                            }

                            continue;
                        }

                        if ($event instanceof TextDelta) {
                            if ($messageId === null) {
                                $messageId = $runId ?? $event->messageId;
                                $this->emit(['type' => 'TEXT_MESSAGE_START', 'timestamp' => $ts, 'messageId' => $messageId, 'role' => 'assistant']);
                            }

                            $contentAccumulator .= $event->delta;
                            $this->emit(['type' => 'TEXT_MESSAGE_CONTENT', 'timestamp' => $ts, 'messageId' => $messageId, 'delta' => $event->delta, 'content' => $contentAccumulator]);

                            continue;
                        }

                        if ($event instanceof StreamEnd) {
                            $finishReason = match ($event->reason ?? '') {
                                'length' => 'length',
                                'content_filter' => 'content_filter',
                                default => 'stop',
                            };
                            $usage = $event->usage;

                            $this->emit(['type' => 'TEXT_MESSAGE_END', 'timestamp' => $ts, 'messageId' => $messageId ?? '']);
                            $this->emit([
                                'type' => 'RUN_FINISHED',
                                'timestamp' => $ts,
                                'runId' => $runId ?? '',
                                'finishReason' => $finishReason,
                                'usage' => [
                                    'promptTokens' => $usage->promptTokens ?? 0,
                                    'completionTokens' => $usage->completionTokens ?? 0,
                                    'totalTokens' => ($usage->promptTokens ?? 0) + ($usage->completionTokens ?? 0),
                                ],
                            ]);
                        }
                    }
                } catch (Throwable $throwable) {
                    $ts = (int) (microtime(true) * 1000);
                    $this->emit([
                        'type' => 'RUN_ERROR',
                        'timestamp' => $ts,
                        'runId' => $runId,
                        'error' => ['message' => $throwable->getMessage(), 'code' => (string) $throwable->getCode()],
                    ]);
                }
            },
            200,
            [
                'Content-Type' => 'application/x-ndjson',
                'Cache-Control' => 'no-cache, no-transform',
                'X-Accel-Buffering' => 'no',
            ],
        );

        // Add CORS headers to the streamed response
        $this->applyCorsHeaders($streamedResponse, $embedToken);

        return $streamedResponse;
    }

    /**
     * Render the standalone public chat page.
     */
    public function standalone(AgentDefinition $agentDefinition): \Illuminate\Contracts\View\View|JsonResponse
    {
        if (! $agentDefinition->embed_enabled) {
            abort(404);
        }

        // Find an active embed token for this agent
        /** @var AgentEmbedToken|null $embedToken */
        $embedToken = $agentDefinition->embedTokens()
            ->where('is_active', true)
            ->first();

        if ($embedToken === null) {
            abort(404);
        }

        $theme = $agentDefinition->embed_theme ?? [];

        return view('embed.standalone', [
            'definition' => $agentDefinition,
            'token' => $embedToken->token,
            'theme' => [
                'primary_color' => $theme['primary_color'] ?? '#0d9488',
                'position' => $theme['position'] ?? 'bottom-right',
                'greeting' => $theme['greeting'] ?? 'Hi! How can I help you?',
                'placeholder' => $theme['placeholder'] ?? 'Type a message...',
                'show_powered_by' => $theme['show_powered_by'] ?? true,
            ],
        ]);
    }

    private function extractPrompt(Request $request): string
    {
        $message = $request->input('message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        /** @var array<int, array{role?: string, content?: mixed}> $messages */
        $messages = $request->input('messages', []);

        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') !== 'user') {
                continue;
            }

            if (isset($m['content']) && is_string($m['content'])) {
                return $m['content'];
            }
        }

        return '';
    }

    /**
     * Get or create a system user for embed interactions.
     */
    private function getOrCreateEmbedUser(\App\Models\Organization $organization): \App\Models\User
    {
        $email = sprintf('embed-bot@org-%d.internal', $organization->id);

        /** @var \App\Models\User $user */
        $user = \App\Models\User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Embed Bot',
                'password' => bcrypt(Str::random(64)),
                'email_verified_at' => now(),
            ],
        );

        return $user;
    }

    /**
     * Resolve prompt variables for embed context.
     */
    private function resolvePromptVariables(
        AgentDefinition $definition,
        \App\Models\Organization $organization,
        \App\Models\User $user,
    ): string {
        return Str::replace(
            ['{{org_name}}', '{{user_name}}', '{{current_date}}'],
            [$organization->name, $user->name, now()->toDateString()],
            $definition->system_prompt,
        );
    }

    /**
     * Emit a single NDJSON line and flush.
     *
     * @param  array<string, mixed>  $data
     */
    private function emit(array $data): void
    {
        echo json_encode($data)."\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    /**
     * Add CORS headers to a JSON response based on the token's allowed domains.
     */
    private function withCorsHeaders(JsonResponse $response, AgentEmbedToken $token): JsonResponse
    {
        $domains = $token->allowed_domains;

        $allowOrigin = ($domains === null || $domains === []) ? '*' : implode(', ', array_map(
            fn (string $domain): string => str_starts_with($domain, '*.') ? $domain : 'https://'.$domain,
            $domains,
        ));

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept');

        return $response;
    }

    /**
     * Apply CORS headers to a streamed response.
     */
    private function applyCorsHeaders(StreamedResponse $response, AgentEmbedToken $token): void
    {
        $domains = $token->allowed_domains;

        $allowOrigin = ($domains === null || $domains === []) ? '*' : implode(', ', array_map(
            fn (string $domain): string => str_starts_with($domain, '*.') ? $domain : 'https://'.$domain,
            $domains,
        ));

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }
}
