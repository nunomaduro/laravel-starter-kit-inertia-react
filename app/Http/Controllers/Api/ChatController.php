<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Ai\Agents\AssistantAgent;
use App\Services\PrismService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Stream chat completion as NDJSON (TanStack AG-UI protocol) for use with fetchHttpStream.
     * Accepts optional conversation_id to continue a conversation; creates one when absent.
     * Messages may have either top-level "content" (string) or "parts" (TanStack UIMessage format).
     */
    public function __invoke(Request $request): Response|StreamedResponse
    {
        $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|string|in:user,assistant,system',
            'conversation_id' => ['nullable', 'string', 'uuid', function (string $attr, string $value, Closure $fail) use ($request): void {
                $user = $request->user();
                if ($user === null) {
                    $fail('Unauthenticated.');

                    return;
                }
                $exists = DB::table('agent_conversations')
                    ->where('id', $value)
                    ->where('user_id', $user->id)
                    ->exists();
                if (! $exists) {
                    $fail('The selected conversation is invalid.');
                }
            }],
        ]);

        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        /** @var array<int, array{role?: string, content?: mixed, parts?: array<int, array{type?: string, content?: string}>}> $messages */
        $messages = $request->input('messages', []);
        $lastUser = self::getMessageContent(array_reverse($messages), 'user');
        $prompt = $lastUser ?? '';

        if ($prompt === '') {
            return response()->json([
                'message' => 'The messages.0.content field is required.',
                'errors' => ['messages' => ['The last user message must have content or text parts.']],
            ], 422);
        }

        $defaultProvider = config('ai.default', 'openai');
        $providerKey = config("ai.providers.{$defaultProvider}.key");
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
            DB::table('agent_conversations')->insert([
                'id' => $newConversationId,
                'user_id' => $user->id,
                'title' => 'New chat',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $conversationId = $newConversationId;
        }

        $agent = AssistantAgent::make(['user_id' => $user->id])
            ->continue($conversationId, $user);

        try {
            $stream = $agent->stream($prompt);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'AI request failed: '.$e->getMessage(),
            ], 502);
        }

        $runId = null;
        $messageId = null;
        $contentAccumulator = '';

        return response()->stream(
            function () use ($stream, $newConversationId, $prompt, &$runId, &$messageId, &$contentAccumulator): void {
                if (ob_get_level()) {
                    ob_end_clean();
                }
                try {
                    foreach ($stream as $event) {
                        if (connection_aborted()) {
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
                                ."User: {$prompt}\n"
                                .'Assistant: '.Str::limit($contentAccumulator, 500);

                            $generatedTitle = Str::limit(
                                mb_trim(app(PrismService::class)->generate($titlePrompt)->text),
                                100,
                            );

                            if ($generatedTitle !== '') {
                                DB::table('agent_conversations')
                                    ->where('id', $newConversationId)
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
                } catch (Throwable $e) {
                    $ts = (int) (microtime(true) * 1000);
                    echo json_encode([
                        'type' => 'RUN_ERROR',
                        'timestamp' => $ts,
                        'runId' => $runId,
                        'error' => ['message' => $e->getMessage(), 'code' => (string) $e->getCode()],
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
    private static function getMessageContent(array $messages, string $role): ?string
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
