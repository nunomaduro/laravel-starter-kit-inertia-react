<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use App\Models\AgentConversation;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\ReasoningStart;
use Laravel\Ai\Streaming\Events\StreamEnd;
use Laravel\Ai\Streaming\Events\StreamStart;
use Laravel\Ai\Streaming\Events\TextDelta;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Services\AgentRunner;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final readonly class AgentChatController
{
    public function __construct(
        private AgentRunner $runner,
    ) {}

    /**
     * Stream a persistent chat response (saved to conversations, billed).
     */
    public function stream(Request $request, AgentDefinition $agentDefinition): StreamedResponse|JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $prompt = $this->extractPrompt($request);

        if ($prompt === '') {
            return response()->json([
                'message' => 'A message is required.',
                'errors' => ['message' => ['The message field is required.']],
            ], 422);
        }

        $conversationId = $request->input('conversation_id');
        $newConversationId = null;

        if (! is_string($conversationId) || $conversationId === '') {
            $newConversationId = (string) Str::uuid();
            AgentConversation::create([
                'id' => $newConversationId,
                'user_id' => $user->id,
                'organization_id' => TenantContext::id(),
                'agent_definition_id' => $agentDefinition->id,
                'title' => 'New chat',
            ]);
            $conversationId = $newConversationId;
        }

        return $this->streamResponse($agentDefinition, $user, $prompt, $conversationId, $newConversationId);
    }

    /**
     * Stream an ephemeral preview response (not saved, not billed).
     */
    public function preview(Request $request, AgentDefinition $agentDefinition): StreamedResponse|JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $prompt = $this->extractPrompt($request);

        if ($prompt === '') {
            return response()->json([
                'message' => 'A message is required.',
                'errors' => ['message' => ['The message field is required.']],
            ], 422);
        }

        return $this->streamResponse($agentDefinition, $user, $prompt, conversationId: null, newConversationId: null);
    }

    /**
     * List conversations for this agent definition scoped to user and org.
     */
    public function conversations(Request $request, AgentDefinition $agentDefinition): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $conversations = AgentConversation::query()
            ->where('agent_definition_id', $agentDefinition->id)
            ->where('user_id', $user->id)
            ->where('organization_id', TenantContext::id())
            ->latest()
            ->paginate(20);

        return response()->json($conversations);
    }

    private function extractPrompt(Request $request): string
    {
        $message = $request->input('message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        /** @var array<int, array{role?: string, content?: mixed, parts?: array<int, array{type?: string, content?: string}>}> $messages */
        $messages = $request->input('messages', []);

        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') !== 'user') {
                continue;
            }

            if (isset($m['content']) && is_string($m['content'])) {
                return $m['content'];
            }

            $parts = $m['parts'] ?? [];

            if (is_array($parts)) {
                $text = [];

                foreach ($parts as $part) {
                    if (($part['type'] ?? '') === 'text' && isset($part['content']) && is_string($part['content'])) {
                        $text[] = $part['content'];
                    }
                }

                if ($text !== []) {
                    return implode('', $text);
                }
            }
        }

        return '';
    }

    /**
     * @param  \App\Models\User  $user
     */
    private function streamResponse(
        AgentDefinition $agentDefinition,
        mixed $user,
        string $prompt,
        ?string $conversationId,
        ?string $newConversationId,
    ): StreamedResponse|JsonResponse {
        $org = TenantContext::organization();

        if ($org === null) {
            return response()->json(['message' => 'Organization context required.'], 403);
        }

        try {
            $stream = $this->runner
                ->forDefinition($agentDefinition)
                ->withUser($user)
                ->withOrganization($org)
                ->stream($prompt);
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => 'AI request failed: '.$throwable->getMessage(),
            ], 502);
        }

        $runId = null;
        $messageId = null;
        $contentAccumulator = '';

        return response()->stream(
            function () use ($stream, $newConversationId, &$runId, &$messageId, &$contentAccumulator): void {
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

                            if ($newConversationId !== null) {
                                $this->emit(['type' => 'CONVERSATION_CREATED', 'timestamp' => $ts, 'conversationId' => $newConversationId]);
                            }

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
}
