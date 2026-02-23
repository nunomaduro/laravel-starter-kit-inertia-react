import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useIsMobile } from '@/hooks/use-mobile';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import type { UIMessage } from '@tanstack/ai-client';
import { fetchHttpStream } from '@tanstack/ai-client';
import { useChat } from '@tanstack/ai-react';
import { AlertCircle, Menu, RefreshCw } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { ChatInput } from './_components/chat-input';
import { ConversationSidebar } from './_components/conversation-sidebar';
import { EmptyState } from './_components/empty-state';
import { MessageList } from './_components/message-list';

type ConversationItem = {
    id: string;
    title: string;
    created_at: string;
    updated_at: string;
};

type ConversationDetail = ConversationItem & {
    messages: Array<{ id: string; role: string; content: string }>;
};

function serverMessageToUIMessage(m: {
    id: string;
    role: string;
    content: string;
}): UIMessage {
    return {
        id: m.id,
        role: m.role as 'user' | 'assistant' | 'system',
        parts: [{ type: 'text', content: m.content ?? '' }],
    };
}

function getConversationIdFromUrl(): string | null {
    if (typeof window === 'undefined') return null;
    const params = new URLSearchParams(window.location.search);
    return params.get('conversation');
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

export default function ChatPage() {
    const { auth } = usePage<SharedData>().props;
    const isMobile = useIsMobile();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [conversationIdFromUrl, setConversationIdFromUrl] = useState<
        string | null
    >(() => getConversationIdFromUrl());
    const conversationIdRef = useRef<string | null>(conversationIdFromUrl);
    const createdConversationIdRef = useRef<string | null>(null);
    const [conversations, setConversations] = useState<ConversationItem[]>([]);
    const [conversationsLoading, setConversationsLoading] = useState(true);
    const [csrfReady, setCsrfReady] = useState(false);

    const syncUrlToState = useCallback(() => {
        const id = getConversationIdFromUrl();
        if (id !== conversationIdFromUrl) {
            setConversationIdFromUrl(id);
        }
    }, [conversationIdFromUrl]);

    useEffect(() => {
        conversationIdRef.current = conversationIdFromUrl;
    }, [conversationIdFromUrl]);

    useEffect(() => {
        const id = setInterval(syncUrlToState, 200);
        return () => clearInterval(id);
    }, [syncUrlToState]);

    useEffect(() => {
        fetch('/sanctum/csrf-cookie', { credentials: 'include' })
            .then((r) => (r.ok ? setCsrfReady(true) : setCsrfReady(false)))
            .catch(() => setCsrfReady(false));
    }, []);

    const connection = useMemo(
        () =>
            fetchHttpStream('/api/chat', () => {
                const token = getCsrfToken();
                const headers: Record<string, string> = {
                    Accept: 'application/json, application/x-ndjson',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                };
                if (token) {
                    headers['X-XSRF-TOKEN'] = token;
                }
                return {
                    credentials: 'include',
                    headers,
                    body: {
                        conversation_id: conversationIdRef.current ?? undefined,
                    },
                    fetchClient: async (
                        url: RequestInfo | URL,
                        init?: RequestInit,
                    ) => {
                        const res = await fetch(url, {
                            ...init,
                            redirect: 'manual',
                        });
                        if (
                            res.type === 'opaqueredirect' ||
                            res.status === 302 ||
                            res.status === 301
                        ) {
                            throw new Error(
                                'Session expired. Please refresh the page (F5 or Cmd+R) and try again.',
                            );
                        }
                        if (!res.ok) {
                            let msg = `${res.status} ${res.statusText}`;
                            try {
                                const ct =
                                    res.headers.get('content-type') ?? '';
                                if (ct.includes('application/json')) {
                                    const json = (await res.json()) as {
                                        message?: string;
                                    };
                                    if (json.message) msg = json.message;
                                }
                            } catch {
                                /* ignore */
                            }
                            throw new Error(msg);
                        }
                        return res;
                    },
                };
            }),
        [],
    );

    const { messages, sendMessage, setMessages, isLoading, error, stop } =
        useChat({
            connection,
            onChunk(chunk: {
                type?: string;
                conversationId?: string;
                title?: string;
            }) {
                if (
                    chunk.type === 'CONVERSATION_CREATED' &&
                    chunk.conversationId
                ) {
                    createdConversationIdRef.current = chunk.conversationId;
                    conversationIdRef.current = chunk.conversationId;
                    setConversationIdFromUrl(chunk.conversationId);
                    window.history.replaceState(
                        {},
                        '',
                        `/chat?conversation=${chunk.conversationId}`,
                    );
                }
                if (
                    chunk.type === 'CONVERSATION_TITLE_UPDATED' &&
                    chunk.conversationId &&
                    chunk.title
                ) {
                    setConversations((prev) =>
                        prev.map((c) =>
                            c.id === chunk.conversationId
                                ? { ...c, title: chunk.title! }
                                : c,
                        ),
                    );
                }
            },
        });

    const loadConversations = useCallback(async () => {
        setConversationsLoading(true);
        try {
            const res = await fetch('/api/conversations', {
                credentials: 'include',
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
        loadConversations();
    }, [loadConversations, conversationIdFromUrl]);

    useEffect(() => {
        const id = conversationIdFromUrl;
        if (!id) {
            setMessages([]);
            return;
        }
        if (createdConversationIdRef.current === id) {
            createdConversationIdRef.current = null;
            return;
        }
        let cancelled = false;
        (async () => {
            try {
                const res = await fetch(`/api/conversations/${id}`, {
                    credentials: 'include',
                });
                if (!res.ok || cancelled) return;
                const json = (await res.json()) as { data: ConversationDetail };
                const list = json.data?.messages ?? [];
                if (!cancelled) {
                    setMessages(list.map(serverMessageToUIMessage));
                }
            } catch {
                if (!cancelled) setMessages([]);
            }
        })();
        return () => {
            cancelled = true;
        };
    }, [conversationIdFromUrl, setMessages]);

    const handleNewChat = useCallback(() => {
        conversationIdRef.current = null;
        setConversationIdFromUrl(null);
        setMessages([]);
        setSidebarOpen(false);
        router.visit('/chat');
    }, [setMessages]);

    const handleSelectConversation = useCallback((id: string) => {
        conversationIdRef.current = id;
        setConversationIdFromUrl(id);
        setSidebarOpen(false);
        router.visit(`/chat?conversation=${id}`);
    }, []);

    const handleConversationRenamed = useCallback(
        (id: string, title: string) => {
            setConversations((prev) =>
                prev.map((c) => (c.id === id ? { ...c, title } : c)),
            );
        },
        [],
    );

    const handleSend = useCallback(
        (content: string) => {
            sendMessage(content);
        },
        [sendMessage],
    );

    return (
        <AppSidebarLayout>
            <Head title="Chat" />
            <div className="flex h-[calc(100vh-4rem)] flex-1 gap-4 overflow-hidden p-4">
                {isMobile ? (
                    <Sheet open={sidebarOpen} onOpenChange={setSidebarOpen}>
                        <SheetContent side="left" className="w-72 p-0">
                            <SheetHeader className="sr-only">
                                <SheetTitle>Conversations</SheetTitle>
                            </SheetHeader>
                            <ConversationSidebar
                                conversations={conversations}
                                conversationsLoading={conversationsLoading}
                                activeConversationId={conversationIdFromUrl}
                                onNewChat={handleNewChat}
                                onSelectConversation={handleSelectConversation}
                                onConversationDeleted={loadConversations}
                                onConversationRenamed={
                                    handleConversationRenamed
                                }
                                isMobile
                            />
                        </SheetContent>
                    </Sheet>
                ) : (
                    <ConversationSidebar
                        conversations={conversations}
                        conversationsLoading={conversationsLoading}
                        activeConversationId={conversationIdFromUrl}
                        onNewChat={handleNewChat}
                        onSelectConversation={handleSelectConversation}
                        onConversationDeleted={loadConversations}
                        onConversationRenamed={handleConversationRenamed}
                    />
                )}

                <div className="flex min-w-0 flex-1 flex-col overflow-hidden rounded-xl border bg-card">
                    {isMobile && (
                        <div className="flex items-center border-b px-3 py-2">
                            <Button
                                variant="ghost"
                                size="icon-xs"
                                onClick={() => setSidebarOpen(true)}
                                data-pan="chat-mobile-menu"
                            >
                                <Menu className="size-4" />
                            </Button>
                        </div>
                    )}
                    {error && <ErrorBanner error={error} />}

                    {messages.length === 0 && !isLoading ? (
                        <EmptyState onSend={handleSend} />
                    ) : (
                        <MessageList
                            messages={messages}
                            isLoading={isLoading}
                            user={auth.user}
                        />
                    )}

                    <ChatInput
                        onSend={handleSend}
                        onStop={stop}
                        isLoading={isLoading}
                        disabled={!csrfReady}
                    />
                </div>
            </div>
        </AppSidebarLayout>
    );
}

function ErrorBanner({ error }: { error: Error }) {
    const is419 = error.message.includes('419');

    return (
        <Alert variant="destructive" className="m-4 mb-0">
            <AlertCircle className="size-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription className="flex items-center gap-2">
                {is419
                    ? 'Session expired. Please refresh the page.'
                    : error.message}
                {is419 && (
                    <button
                        type="button"
                        onClick={() => window.location.reload()}
                        className="inline-flex items-center gap-1 text-xs underline"
                    >
                        <RefreshCw className="size-3" />
                        Refresh
                    </button>
                )}
            </AlertDescription>
        </Alert>
    );
}
