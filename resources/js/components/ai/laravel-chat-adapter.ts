import type {
    ChatModelAdapter,
    ChatModelRunOptions,
    ChatModelRunResult,
    ThreadMessage,
} from '@assistant-ui/react';

export interface LaravelAdapterConfig {
    endpoint: string;
    model?: string;
    headers?: Record<string, string>;
    systemPrompt?: string;
}

function getCsrfToken(): string {
    if (typeof document === 'undefined') return '';
    const meta = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    );
    return meta?.content ?? '';
}

function messageToApiFormat(message: ThreadMessage): { role: string; content: string } {
    const parts = message.content ?? [];
    const text = parts
        .filter((p): p is { type: 'text'; text: string } => p.type === 'text')
        .map((p) => p.text)
        .join('\n');
    return { role: message.role, content: text };
}

export function createLaravelChatAdapter(
    config: LaravelAdapterConfig,
): ChatModelAdapter {
    return {
        async *run(options: ChatModelRunOptions): AsyncGenerator<ChatModelRunResult> {
            const { messages, abortSignal } = options;
            const apiMessages = messages.map(messageToApiFormat);
            const body: Record<string, unknown> = {
                messages: [
                    ...(config.systemPrompt
                        ? [{ role: 'system', content: config.systemPrompt }]
                        : []),
                    ...apiMessages,
                ],
            };
            if (config.model) body.model = config.model;

            const response = await fetch(config.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'text/event-stream,application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    ...config.headers,
                },
                body: JSON.stringify(body),
                signal: abortSignal,
            });

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`,
                );
            }

            const contentType = response.headers.get('content-type') ?? '';
            if (contentType.includes('text/event-stream')) {
                const reader = response.body?.getReader();
                if (!reader) throw new Error('No response body');
                const decoder = new TextDecoder();
                let accumulated = '';
                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;
                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n');
                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = line.slice(6).trim();
                            if (data === '[DONE]') continue;
                            try {
                                const parsed = JSON.parse(data) as {
                                    content?: string;
                                    delta?: string;
                                    text?: string;
                                };
                                const delta =
                                    parsed.delta ??
                                    parsed.content ??
                                    parsed.text ??
                                    '';
                                accumulated += delta;
                                yield { content: [{ type: 'text', text: accumulated }] };
                            } catch {
                                // skip invalid JSON lines
                            }
                        }
                    }
                }
                return;
            }

            const data = (await response.json()) as { content?: string; text?: string; message?: { content?: string } };
            const text =
                data.content ??
                data.text ??
                data.message?.content ??
                '';
            yield { content: [{ type: 'text', text }] };
        },
    };
}
