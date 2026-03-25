<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Ai\Agents\AssistantAgent;
use App\Ai\Agents\OrgScopedAgent;
use App\Http\Requests\Api\StoreChatMessageRequest;
use App\Models\AgentConversation;
use App\Services\PrismService;
use App\Services\TenantContext;
use Illuminate\Support\Str;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\ReasoningStart;
use Laravel\Ai\Streaming\Events\StreamEnd;
use Laravel\Ai\Streaming\Events\StreamStart;
use Laravel\Ai\Streaming\Events\TextDelta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

use function array_reverse;
use function is_array;

final class ChatController
{
    /**
     * Stream an AI chat response as NDJSON (TanStack AG-UI protocol).
     *
     * Accepts an optional conversation_id to continue an existing conversation; a new conversation
     * is created automatically when absent. Messages may carry either a top-level "content" string
     * or a "parts" array (TanStack UIMessage format).
     *
     * Each newline-delimited JSON line is one of: RUN_STARTED, CONVERSATION_CREATED,
     * TEXT_MESSAGE_START, TEXT_MESSAGE_CONTENT, TEXT_MESSAGE_END, CONVERSATION_TITLE_UPDATED,
     * RUN_FINISHED, or RUN_ERROR.
     */
    public function __invoke(StoreChatMessageRequest $request): Response|StreamedResponse
    {

        $user = $request->user();
        abort_if($user === null, 401);

        /** @var array<int, array{role?: string, content?: mixed, parts?: array<int, array{type?: string, content?: string}>}> $messages */
        $messages = $request->input('messages', []);
        $lastUser = $this->getMessageContent(array_reverse($messages), 'user');
        $prompt = $lastUser ?? '';

        if ($prompt === '') {
            return response()->json([
                'message' => 'The messages.0.content field is required.',
                'errors' => ['messages' => ['The last user message must have content or text parts.']],
            ], 422);
        }

        $defaultProvider = config('ai.default', 'openai');
        $providerKey = config(sprintf('ai.providers.%s.key', $defaultProvider));
        if (empty($providerKey)) {
            $envKey = mb_strtoupper(str_replace('-', '_', (string) $defaultProvider)).'_API_KEY';

            return response()->json([
                'message' => 'AI provider is not configured. Set '.$envKey.' in your .env.',
            ], 503);
        }

        $conversationIdInput = $request->input('conversation_id');
        $newConversationId = null;
        $conversationId = is_string($conversationIdInput) && $conversationIdInput !== '' ? $conversationIdInput : null;

        if ($conversationId === null) {
            $newConversationId = (string) Str::uuid();
            $conversationData = [
                'id' => $newConversationId,
                'user_id' => $user->id,
                'title' => 'New chat',
            ];

            if (TenantContext::check()) {
                $conversationData['organization_id'] = TenantContext::id();
            }

            AgentConversation::create($conversationData);
            $conversationId = $newConversationId;
        }

        /** @var array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string} $context */
        $context = $request->input('context', []);

        if (TenantContext::check()) {
            $agent = OrgScopedAgent::make()
                ->withContext($context)
                ->continue($conversationId, $user);
        } else {
            $agent = AssistantAgent::make(['user_id' => $user->id])
                ->continue($conversationId, $user);
        }

        try {
            $stream = $agent->stream($prompt);
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => 'AI request failed: '.$throwable->getMessage(),
            ], 502);
        }

        $runId = null;
        $messageId = null;
        $contentAccumulator = '';

        return response()->stream(
            function () use ($stream, $newConversationId, $prompt, &$runId, &$messageId, &$contentAccumulator): void {
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
                            echo json_encode([
                                'type' => 'RUN_STARTED',
                                'timestamp' => $ts,
                                'runId' => $runId,
                            ])."\n";
                            if ($newConversationId !== null) {
                                echo json_encode([
                                    'type' => 'CONVERSATION_CREATED',
                                    'timestamp' => $ts,
                                    'conversationId' => $newConversationId,
                                ])."\n";
                            }

                            if (ob_get_level() > 0) {
                                ob_flush();
                            }

                            flush();

                            continue;
                        }

                        if ($event instanceof ReasoningStart) {
                            if ($messageId === null) {
                                $messageId = $runId ?? $event->reasoningId;
                                echo json_encode([
                                    'type' => 'TEXT_MESSAGE_START',
                                    'timestamp' => $ts,
                                    'messageId' => $messageId,
                                    'role' => 'assistant',
                                ])."\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }

                                flush();
                            }

                            continue;
                        }

                        if ($event instanceof ReasoningDelta) {
                            if ($messageId === null) {
                                $messageId = $runId ?? $event->reasoningId;
                                echo json_encode([
                                    'type' => 'TEXT_MESSAGE_START',
                                    'timestamp' => $ts,
                                    'messageId' => $messageId,
                                    'role' => 'assistant',
                                ])."\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }

                                flush();
                            }

                            $contentAccumulator .= $event->delta;
                            echo json_encode([
                                'type' => 'TEXT_MESSAGE_CONTENT',
                                'timestamp' => $ts,
                                'messageId' => $messageId,
                                'delta' => $event->delta,
                                'content' => $contentAccumulator,
                            ])."\n";
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }

                            flush();

                            continue;
                        }

                        if ($event instanceof TextDelta) {
                            if ($messageId === null) {
                                $messageId = $runId ?? $event->messageId;
                                echo json_encode([
                                    'type' => 'TEXT_MESSAGE_START',
                                    'timestamp' => $ts,
                                    'messageId' => $messageId,
                                    'role' => 'assistant',
                                ])."\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }

                                flush();
                            }

                            $contentAccumulator .= $event->delta;
                            echo json_encode([
                                'type' => 'TEXT_MESSAGE_CONTENT',
                                'timestamp' => $ts,
                                'messageId' => $messageId,
                                'delta' => $event->delta,
                                'content' => $contentAccumulator,
                            ])."\n";
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }

                            flush();

                            continue;
                        }

                        if ($event instanceof StreamEnd) {
                            $finishReason = match ($event->reason ?? '') {
                                'length' => 'length',
                                'content_filter' => 'content_filter',
                                default => 'stop',
                            };
                            $usage = $event->usage;
                            echo json_encode([
                                'type' => 'TEXT_MESSAGE_END',
                                'timestamp' => $ts,
                                'messageId' => $messageId ?? '',
                            ])."\n";
                            echo json_encode([
                                'type' => 'RUN_FINISHED',
                                'timestamp' => $ts,
                                'runId' => $runId ?? '',
                                'finishReason' => $finishReason,
                                'usage' => [
                                    'promptTokens' => $usage->promptTokens ?? 0,
                                    'completionTokens' => $usage->completionTokens ?? 0,
                                    'totalTokens' => ($usage->promptTokens ?? 0) + ($usage->completionTokens ?? 0),
                                ],
                            ])."\n";
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }

                            flush();
                        }
                    }

                    // Generate AI title for new conversations after stream completes
                    if ($newConversationId !== null && $contentAccumulator !== '') {
                        try {
                            $titlePrompt = 'Generate a concise 3-5 word title for this conversation. '
                                ."Reply with ONLY the title, no quotes or punctuation at the end.\n\n"
                                .sprintf('User: %s%s', $prompt, PHP_EOL)
                                .'Assistant: '.Str::limit($contentAccumulator, 500);

                            $generatedTitle = Str::limit(
                                mb_trim(resolve(PrismService::class)->generate($titlePrompt)->text),
                                100,
                            );

                            if ($generatedTitle !== '') {
                                AgentConversation::where('id', $newConversationId)
                                    ->update(['title' => $generatedTitle, 'updated_at' => now()]);

                                $ts = (int) (microtime(true) * 1000);
                                echo json_encode([
                                    'type' => 'CONVERSATION_TITLE_UPDATED',
                                    'timestamp' => $ts,
                                    'conversationId' => $newConversationId,
                                    'title' => $generatedTitle,
                                ])."\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }

                                flush();
                            }
                        } catch (Throwable) {
                            // Silent fail — "New chat" title remains
                        }
                    }
                } catch (Throwable $throwable) {
                    $ts = (int) (microtime(true) * 1000);
                    echo json_encode([
                        'type' => 'RUN_ERROR',
                        'timestamp' => $ts,
                        'runId' => $runId,
                        'error' => ['message' => $throwable->getMessage(), 'code' => (string) $throwable->getCode()],
                    ])."\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }

                    flush();
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
     * Extract text content from a message that may have "content" (string) or "parts" (TanStack UIMessage).
     *
     * @param  array<int, array{role?: string, content?: mixed, parts?: array<int, array{type?: string, content?: string}>}>  $messages
     */
    private function getMessageContent(array $messages, string $role): ?string
    {
        foreach ($messages as $m) {
            if (($m['role'] ?? '') !== $role) {
                continue;
            }

            if (isset($m['content']) && \is_string($m['content'])) {
                return $m['content'];
            }

            $parts = $m['parts'] ?? [];
            if (is_array($parts)) {
                $text = [];
                foreach ($parts as $part) {
                    if (($part['type'] ?? '') === 'text' && isset($part['content']) && \is_string($part['content'])) {
                        $text[] = $part['content'];
                    }
                }

                if ($text !== []) {
                    return implode('', $text);
                }
            }
        }

        return null;
    }
}
