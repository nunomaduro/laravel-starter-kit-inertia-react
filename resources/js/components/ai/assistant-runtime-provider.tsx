import * as React from 'react';

export interface AssistantMessage {
    id: string;
    role: 'user' | 'assistant' | 'system';
    content: string;
    isStreaming?: boolean;
    toolCalls?: ToolCall[];
    tokenUsage?: TokenUsage;
    createdAt: Date;
}

export interface ToolCall {
    id: string;
    name: string;
    input: Record<string, unknown>;
    output?: string;
    status: 'pending' | 'running' | 'done' | 'error';
}

export interface TokenUsage {
    prompt: number;
    completion: number;
    total: number;
}

export interface AssistantRuntimeConfig {
    /** POST endpoint for chat completions (Laravel AI SDK route). */
    endpoint: string;
    /** Model identifier passed to backend (optional). */
    model?: string;
    /** Additional headers (e.g. CSRF token). */
    headers?: Record<string, string>;
    /** System prompt sent as first message. */
    systemPrompt?: string;
}

export interface AssistantRuntimeContextValue {
    messages: AssistantMessage[];
    isLoading: boolean;
    config: AssistantRuntimeConfig;
    append: (content: string) => Promise<void>;
    stop: () => void;
    clear: () => void;
    setConfig: React.Dispatch<React.SetStateAction<AssistantRuntimeConfig>>;
}

export const AssistantRuntimeContext = React.createContext<AssistantRuntimeContextValue | null>(null);

export interface AssistantRuntimeProviderProps {
    children: React.ReactNode;
    endpoint: string;
    model?: string;
    headers?: Record<string, string>;
    systemPrompt?: string;
}

function generateId(): string {
    return Math.random().toString(36).slice(2, 11);
}

function getCsrfToken(): string {
    if (typeof document === 'undefined') return '';
    const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
    return meta?.content ?? '';
}

export function AssistantRuntimeProvider({
    children,
    endpoint,
    model,
    headers,
    systemPrompt,
}: AssistantRuntimeProviderProps) {
    const [messages, setMessages] = React.useState<AssistantMessage[]>([]);
    const [isLoading, setIsLoading] = React.useState(false);
    const [config, setConfig] = React.useState<AssistantRuntimeConfig>({
        endpoint,
        model,
        headers,
        systemPrompt,
    });

    const abortControllerRef = React.useRef<AbortController | null>(null);

    // Keep config in sync with props
    React.useEffect(() => {
        setConfig((prev) => ({ ...prev, endpoint, model, headers, systemPrompt }));
    }, [endpoint, model, headers, systemPrompt]);

    const append = React.useCallback(
        async (content: string) => {
            if (isLoading) return;

            const userMessage: AssistantMessage = {
                id: generateId(),
                role: 'user',
                content,
                createdAt: new Date(),
            };

            const assistantId = generateId();
            const assistantMessage: AssistantMessage = {
                id: assistantId,
                role: 'assistant',
                content: '',
                isStreaming: true,
                createdAt: new Date(),
            };

            setMessages((prev) => [...prev, userMessage, assistantMessage]);
            setIsLoading(true);

            const controller = new AbortController();
            abortControllerRef.current = controller;

            try {
                const history = messages
                    .filter((m) => m.role !== 'system')
                    .map((m) => ({ role: m.role, content: m.content }));

                const body: Record<string, unknown> = {
                    messages: [
                        ...(config.systemPrompt
                            ? [{ role: 'system', content: config.systemPrompt }]
                            : []),
                        ...history,
                        { role: 'user', content },
                    ],
                };

                if (config.model) {
                    body.model = config.model;
                }

                const response = await fetch(config.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'text/event-stream,application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        ...config.headers,
                    },
                    body: JSON.stringify(body),
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type') ?? '';

                if (contentType.includes('text/event-stream')) {
                    // Handle SSE / streaming response
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
                                        parsed.delta ?? parsed.content ?? parsed.text ?? '';
                                    accumulated += delta;
                                    setMessages((prev) =>
                                        prev.map((m) =>
                                            m.id === assistantId
                                                ? { ...m, content: accumulated }
                                                : m,
                                        ),
                                    );
                                } catch {
                                    // non-JSON data line, treat as raw text
                                    accumulated += data;
                                    setMessages((prev) =>
                                        prev.map((m) =>
                                            m.id === assistantId
                                                ? { ...m, content: accumulated }
                                                : m,
                                        ),
                                    );
                                }
                            }
                        }
                    }
                } else {
                    // Handle plain JSON response
                    const json = (await response.json()) as {
                        content?: string;
                        message?: string;
                        response?: string;
                        usage?: { prompt_tokens?: number; completion_tokens?: number; total_tokens?: number };
                    };
                    const text =
                        json.content ?? json.message ?? json.response ?? '';
                    const usage = json.usage;
                    setMessages((prev) =>
                        prev.map((m) =>
                            m.id === assistantId
                                ? {
                                      ...m,
                                      content: text,
                                      tokenUsage: usage
                                          ? {
                                                prompt: usage.prompt_tokens ?? 0,
                                                completion: usage.completion_tokens ?? 0,
                                                total: usage.total_tokens ?? 0,
                                            }
                                          : undefined,
                                  }
                                : m,
                        ),
                    );
                }

                // Mark as done streaming
                setMessages((prev) =>
                    prev.map((m) =>
                        m.id === assistantId ? { ...m, isStreaming: false } : m,
                    ),
                );
            } catch (err) {
                if ((err as Error).name === 'AbortError') {
                    // Stopped by user
                    setMessages((prev) =>
                        prev.map((m) =>
                            m.id === assistantId ? { ...m, isStreaming: false } : m,
                        ),
                    );
                } else {
                    const errorText =
                        err instanceof Error ? err.message : 'An error occurred.';
                    setMessages((prev) =>
                        prev.map((m) =>
                            m.id === assistantId
                                ? {
                                      ...m,
                                      content:
                                          (m.content || '') +
                                          `\n\n*Error: ${errorText}*`,
                                      isStreaming: false,
                                  }
                                : m,
                        ),
                    );
                }
            } finally {
                setIsLoading(false);
                abortControllerRef.current = null;
            }
        },
        [isLoading, messages, config],
    );

    const stop = React.useCallback(() => {
        abortControllerRef.current?.abort();
    }, []);

    const clear = React.useCallback(() => {
        stop();
        setMessages([]);
    }, [stop]);

    const value = React.useMemo<AssistantRuntimeContextValue>(
        () => ({ messages, isLoading, config, append, stop, clear, setConfig }),
        [messages, isLoading, config, append, stop, clear],
    );

    return (
        <AssistantRuntimeContext.Provider value={value}>
            {children}
        </AssistantRuntimeContext.Provider>
    );
}

export function useAssistantRuntime(): AssistantRuntimeContextValue {
    const ctx = React.useContext(AssistantRuntimeContext);
    if (!ctx) {
        throw new Error(
            'useAssistantRuntime must be used within an AssistantRuntimeProvider',
        );
    }
    return ctx;
}
