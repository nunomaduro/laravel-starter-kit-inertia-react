import { useAgentContext } from '@/hooks/use-agent-context';
import { Link } from '@inertiajs/react';
import { Maximize2, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { ChatPanel, type ChatMessage } from './chat-panel';
import { ConversationList, type ConversationItem } from './conversation-list';

interface ChatSlideOverProps {
    open: boolean;
    onClose: () => void;
    onUnreadChange?: (count: number) => void;
}

function getCsrfToken(): string {
    if (typeof document === 'undefined') return '';
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    const raw = match ? match[1] : '';
    try {
        return raw ? decodeURIComponent(raw) : '';
    } catch {
        return raw;
    }
}

export function ChatSlideOver({ open, onClose, onUnreadChange: _onUnreadChange }: ChatSlideOverProps) {
    const panelRef = useRef<HTMLDivElement>(null);
    const agentContext = useAgentContext();

    // State
    const [conversations, setConversations] = useState<ConversationItem[]>([]);
    const [conversationsLoading, setConversationsLoading] = useState(true);
    const [activeConversationId, setActiveConversationId] = useState<string | null>(null);
    const [messages, setMessages] = useState<ChatMessage[]>([]);
    const [isStreaming, setIsStreaming] = useState(false);
    const [streamingContent, setStreamingContent] = useState('');
    const activeConversationIdRef = useRef<string | null>(null);
    const abortRef = useRef<AbortController | null>(null);

    // Keep ref in sync
    useEffect(() => {
        activeConversationIdRef.current = activeConversationId;
    }, [activeConversationId]);

    // Escape key
    useEffect(() => {
        if (!open) return;
        function handleEsc(e: KeyboardEvent) {
            if (e.key === 'Escape') onClose();
        }
        window.addEventListener('keydown', handleEsc);
        return () => window.removeEventListener('keydown', handleEsc);
    }, [open, onClose]);

    // Ensure CSRF
    useEffect(() => {
        if (!open) return;
        fetch('/sanctum/csrf-cookie', { credentials: 'include' }).catch(() => {});
    }, [open]);

    // Load conversations
    const loadConversations = useCallback(async () => {
        setConversationsLoading(true);
        try {
            const res = await fetch('/api/conversations', {
                credentials: 'include',
                headers: { Accept: 'application/json' },
            });
            if (res.ok) {
                const json = (await res.json()) as { data: ConversationItem[] };
                setConversations(json.data ?? []);
            }
        } finally {
            setConversationsLoading(false);
        }
    }, []);

    useEffect(() => {
        if (open) loadConversations();
    }, [open, loadConversations]);

    // Load conversation messages
    useEffect(() => {
        if (!activeConversationId) {
            setMessages([]);
            return;
        }
        let cancelled = false;
        (async () => {
            try {
                const res = await fetch(`/api/conversations/${activeConversationId}`, {
                    credentials: 'include',
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok || cancelled) return;
                const json = (await res.json()) as {
                    data: {
                        messages: Array<{ id: string; role: string; content: string }>;
                    };
                };
                if (!cancelled) {
                    setMessages(
                        (json.data?.messages ?? []).map((m) => ({
                            id: m.id,
                            role: m.role as ChatMessage['role'],
                            content: m.content ?? '',
                        })),
                    );
                }
            } catch {
                if (!cancelled) setMessages([]);
            }
        })();
        return () => {
            cancelled = true;
        };
    }, [activeConversationId]);

    // Send message
    const handleSend = useCallback(
        async (content: string) => {
            if (!content.trim() || isStreaming) return;

            // Add user message optimistically
            const userMsg: ChatMessage = {
                id: `tmp-${Date.now()}`,
                role: 'user',
                content: content.trim(),
            };
            setMessages((prev) => [...prev, userMsg]);
            setIsStreaming(true);
            setStreamingContent('');

            const controller = new AbortController();
            abortRef.current = controller;

            try {
                const token = getCsrfToken();
                const headers: Record<string, string> = {
                    Accept: 'application/x-ndjson',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                };
                if (token) headers['X-XSRF-TOKEN'] = token;

                const res = await fetch('/api/chat', {
                    method: 'POST',
                    credentials: 'include',
                    headers,
                    body: JSON.stringify({
                        messages: [{ role: 'user', content: content.trim() }],
                        conversation_id: activeConversationIdRef.current ?? undefined,
                        context: agentContext,
                    }),
                    signal: controller.signal,
                });

                if (!res.ok) {
                    throw new Error(`${res.status} ${res.statusText}`);
                }

                const reader = res.body?.getReader();
                if (!reader) throw new Error('No response body');

                const decoder = new TextDecoder();
                let buffer = '';
                let accumulated = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() ?? '';

                    for (const line of lines) {
                        if (!line.trim()) continue;
                        try {
                            const chunk = JSON.parse(line) as {
                                type?: string;
                                content?: string;
                                conversationId?: string;
                                title?: string;
                            };

                            switch (chunk.type) {
                                case 'CONVERSATION_CREATED':
                                    if (chunk.conversationId) {
                                        activeConversationIdRef.current = chunk.conversationId;
                                        setActiveConversationId(chunk.conversationId);
                                    }
                                    break;
                                case 'TEXT_MESSAGE_CONTENT':
                                    if (chunk.content) {
                                        accumulated += chunk.content;
                                        setStreamingContent(accumulated);
                                    }
                                    break;
                                case 'CONVERSATION_TITLE_UPDATED':
                                    if (chunk.conversationId && chunk.title) {
                                        setConversations((prev) =>
                                            prev.map((c) =>
                                                c.id === chunk.conversationId
                                                    ? { ...c, title: chunk.title! }
                                                    : c,
                                            ),
                                        );
                                    }
                                    break;
                                case 'RUN_FINISHED':
                                case 'TEXT_MESSAGE_END':
                                    // Finalize
                                    break;
                            }
                        } catch {
                            // Skip malformed lines
                        }
                    }
                }

                // Finalize assistant message
                if (accumulated) {
                    const assistantMsg: ChatMessage = {
                        id: `assistant-${Date.now()}`,
                        role: 'assistant',
                        content: accumulated,
                    };
                    setMessages((prev) => [...prev, assistantMsg]);
                }

                // Refresh conversation list
                loadConversations();
            } catch (err) {
                if ((err as Error).name !== 'AbortError') {
                    const errorMsg: ChatMessage = {
                        id: `error-${Date.now()}`,
                        role: 'assistant',
                        content: `Error: ${(err as Error).message}`,
                    };
                    setMessages((prev) => [...prev, errorMsg]);
                }
            } finally {
                setIsStreaming(false);
                setStreamingContent('');
                abortRef.current = null;
            }
        },
        [isStreaming, agentContext, loadConversations],
    );

    const handleStop = useCallback(() => {
        abortRef.current?.abort();
    }, []);

    const handleNewChat = useCallback(() => {
        activeConversationIdRef.current = null;
        setActiveConversationId(null);
        setMessages([]);
    }, []);

    const handleSelectConversation = useCallback((id: string) => {
        activeConversationIdRef.current = id;
        setActiveConversationId(id);
    }, []);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-black/40 transition-opacity duration-200"
                onClick={onClose}
                aria-hidden
            />

            {/* Panel */}
            <div
                ref={panelRef}
                className="absolute top-0 right-0 flex h-full w-[560px] max-w-[calc(100vw-2rem)] flex-col border-l bg-background transition-transform duration-200"
                role="dialog"
                aria-label="AI Assistant"
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b px-4 py-3">
                    <h2 className="font-mono text-sm font-semibold tracking-tight">
                        AI Assistant
                    </h2>
                    <div className="flex items-center gap-1">
                        <Link
                            href="/chat"
                            className="rounded-md p-1.5 text-muted-foreground transition-colors duration-100 hover:bg-muted hover:text-foreground"
                            aria-label="Open full page chat"
                            data-pan="global-chat-expand"
                        >
                            <Maximize2 className="size-4" />
                        </Link>
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-md p-1.5 text-muted-foreground transition-colors duration-100 hover:bg-muted hover:text-foreground"
                            aria-label="Close chat"
                            data-pan="global-chat-close"
                        >
                            <X className="size-4" />
                        </button>
                    </div>
                </div>

                {/* 2-panel body */}
                <div className="flex min-h-0 flex-1">
                    <ConversationList
                        activeId={activeConversationId}
                        onSelect={handleSelectConversation}
                        onNewChat={handleNewChat}
                        conversations={conversations}
                        loading={conversationsLoading}
                    />
                    <div className="flex min-w-0 flex-1 flex-col">
                        <ChatPanel
                            messages={messages}
                            isStreaming={isStreaming}
                            streamingContent={streamingContent}
                        />
                        {/* Chat input integrated here */}
                        <ChatInputBar
                            onSend={handleSend}
                            onStop={handleStop}
                            isStreaming={isStreaming}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}

function ChatInputBar({
    onSend,
    onStop,
    isStreaming,
}: {
    onSend: (content: string) => void;
    onStop: () => void;
    isStreaming: boolean;
}) {
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    const adjustHeight = useCallback(() => {
        const el = textareaRef.current;
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = `${Math.min(el.scrollHeight, 96)}px`;
    }, []);

    const handleSubmit = useCallback(() => {
        const el = textareaRef.current;
        if (!el) return;
        const content = el.value.trim();
        if (!content || isStreaming) return;
        onSend(content);
        el.value = '';
        el.style.height = 'auto';
    }, [onSend, isStreaming]);

    const handleKeyDown = useCallback(
        (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSubmit();
            }
        },
        [handleSubmit],
    );

    return (
        <div className="border-t px-3 py-2" data-pan="global-chat-input">
            <div className="flex items-end gap-2">
                <textarea
                    ref={textareaRef}
                    placeholder="Type a message..."
                    rows={1}
                    onInput={adjustHeight}
                    onKeyDown={handleKeyDown}
                    className="flex max-h-24 min-h-8 flex-1 resize-none rounded-lg border bg-transparent px-3 py-1.5 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-[oklch(0.65_0.14_165)] focus-visible:outline-none"
                />
                {isStreaming ? (
                    <button
                        type="button"
                        onClick={onStop}
                        className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-destructive text-white transition-colors duration-100"
                    >
                        <span className="size-3 rounded-sm bg-white" />
                    </button>
                ) : (
                    <button
                        type="button"
                        onClick={handleSubmit}
                        className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-[oklch(0.65_0.14_165)] text-white transition-colors duration-100 hover:bg-[oklch(0.72_0.14_165)]"
                    >
                        <svg className="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M12 19V5M5 12l7-7 7 7" />
                        </svg>
                    </button>
                )}
            </div>
            <p className="mt-1 text-center text-[10px] text-muted-foreground/50">
                Enter to send, Shift+Enter for newline
            </p>
        </div>
    );
}
