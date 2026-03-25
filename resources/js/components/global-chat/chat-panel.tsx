import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useAgentContext } from '@/hooks/use-agent-context';
import { useInitials } from '@/hooks/use-initials';
import type { SharedData, User } from '@/types';
import { usePage } from '@inertiajs/react';
import { Bot, Copy, Check } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import Markdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { toast } from 'sonner';
import { renderBlock } from '@/components/chat/renderers/renderer-registry';
import '@/components/chat/renderers/text-renderer';
import '@/components/chat/renderers/table-renderer';
import '@/components/chat/renderers/card-renderer';
import '@/components/chat/renderers/chart-renderer';
import '@/components/chat/renderers/action-renderer';
import { VoiceOutput } from './voice-output';

export interface ChatMessage {
    id: string;
    role: 'user' | 'assistant' | 'system';
    content: string;
    blocks?: Array<{ type: string; data: Record<string, unknown> }>;
}

interface ChatPanelProps {
    messages: ChatMessage[];
    isStreaming: boolean;
    streamingContent: string;
}

export function ChatPanel({ messages, isStreaming, streamingContent }: ChatPanelProps) {
    const { auth } = usePage<SharedData>().props;
    const agentContext = useAgentContext();
    const scrollRef = useRef<HTMLDivElement>(null);
    const [showScrollBtn, setShowScrollBtn] = useState(false);

    const isNearBottom = useCallback(() => {
        const el = scrollRef.current;
        if (!el) return true;
        return el.scrollHeight - el.scrollTop - el.clientHeight < 80;
    }, []);

    const scrollToBottom = useCallback(() => {
        const el = scrollRef.current;
        if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
    }, []);

    useEffect(() => {
        if (isNearBottom()) scrollToBottom();
    }, [messages, streamingContent, isNearBottom, scrollToBottom]);

    const handleScroll = useCallback(() => {
        setShowScrollBtn(!isNearBottom());
    }, [isNearBottom]);

    return (
        <div className="flex flex-1 flex-col overflow-hidden">
            {/* Context bar */}
            {agentContext.entity_type && (
                <div className="border-b px-3 py-1.5">
                    <span className="font-mono text-[10px] uppercase tracking-wider text-muted-foreground">
                        // {agentContext.entity_type}
                        {agentContext.entity_name ? `: ${agentContext.entity_name}` : ''}
                    </span>
                </div>
            )}

            {/* Messages */}
            <div className="relative min-h-0 flex-1">
                <div
                    ref={scrollRef}
                    onScroll={handleScroll}
                    className="absolute inset-0 overflow-y-auto px-3 py-3"
                >
                    <div className="space-y-3">
                        {messages.map((m) => (
                            <MessageBubble key={m.id} message={m} user={auth.user} />
                        ))}
                        {isStreaming && streamingContent && (
                            <AssistantMessage content={streamingContent} isStreaming />
                        )}
                        {isStreaming && !streamingContent && <StreamingDots />}
                    </div>
                </div>
                {showScrollBtn && (
                    <button
                        type="button"
                        onClick={scrollToBottom}
                        className="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full border bg-background px-2 py-1 text-[10px] text-muted-foreground transition-opacity hover:bg-muted"
                    >
                        Scroll to bottom
                    </button>
                )}
            </div>
        </div>
    );
}

function MessageBubble({ message, user }: { message: ChatMessage; user: User }) {
    if (message.role === 'user') {
        return <UserMessage content={message.content} user={user} />;
    }
    return (
        <AssistantMessage
            content={message.content}
            blocks={message.blocks}
        />
    );
}

function UserMessage({ content, user }: { content: string; user: User }) {
    const getInitials = useInitials();
    return (
        <div className="flex items-start justify-end gap-2">
            <div className="max-w-[85%] rounded-xl rounded-tr-sm bg-primary px-3 py-2 text-primary-foreground">
                <p className="text-xs break-words whitespace-pre-wrap">{content}</p>
            </div>
            <Avatar className="size-6 shrink-0">
                {user.avatar && <AvatarImage src={user.avatar} alt={user.name} />}
                <AvatarFallback className="text-[9px]">
                    {getInitials(user.name)}
                </AvatarFallback>
            </Avatar>
        </div>
    );
}

function AssistantMessage({
    content,
    blocks,
    isStreaming,
}: {
    content: string;
    blocks?: Array<{ type: string; data: Record<string, unknown> }>;
    isStreaming?: boolean;
}) {
    return (
        <div className="group flex items-start gap-2">
            <div className="flex size-6 shrink-0 items-center justify-center rounded-full bg-muted">
                <Bot className="size-3 text-muted-foreground" />
            </div>
            <div className="relative max-w-[85%] rounded-xl rounded-tl-sm bg-muted px-3 py-2">
                <div className="absolute -top-2 -right-1 flex gap-0.5">
                    <VoiceOutput text={content} />
                    <CopyAction text={content} />
                </div>
                <div className="prose prose-sm dark:prose-invert max-w-none text-xs [&_code]:rounded [&_code]:bg-background/50 [&_code]:px-1 [&_code]:py-0.5 [&_code]:text-[10px] [&_p]:text-xs [&_pre]:overflow-x-auto [&_pre]:rounded-md [&_pre]:bg-background/50 [&_pre]:p-2 [&_pre_code]:bg-transparent [&_pre_code]:p-0">
                    <Markdown remarkPlugins={[remarkGfm]}>{content}</Markdown>
                    {isStreaming && (
                        <span className="inline-block size-1.5 animate-pulse rounded-full bg-[oklch(0.65_0.14_165)]" />
                    )}
                </div>
                {blocks?.map((block, i) => (
                    <div key={i} className="mt-2">
                        {renderBlock(block.type, block.data, `block-${i}`)}
                    </div>
                ))}
            </div>
        </div>
    );
}

function CopyAction({ text }: { text: string }) {
    const [copied, setCopied] = useState(false);
    const timerRef = useRef<ReturnType<typeof setTimeout>>();

    const handleCopy = useCallback(() => {
        navigator.clipboard.writeText(text).then(() => {
            setCopied(true);
            toast.success('Copied');
            if (timerRef.current) clearTimeout(timerRef.current);
            timerRef.current = setTimeout(() => setCopied(false), 2000);
        });
    }, [text]);

    return (
        <button
            type="button"
            onClick={handleCopy}
            className="absolute -top-2 -right-1 rounded p-0.5 text-muted-foreground opacity-0 transition-opacity duration-100 hover:text-foreground group-hover:opacity-100"
            aria-label="Copy message"
        >
            {copied ? <Check className="size-3" /> : <Copy className="size-3" />}
        </button>
    );
}

function StreamingDots() {
    return (
        <div className="flex items-start gap-2">
            <div className="flex size-6 shrink-0 items-center justify-center rounded-full bg-muted">
                <Bot className="size-3 text-muted-foreground" />
            </div>
            <div className="rounded-xl rounded-tl-sm bg-muted px-3 py-2.5">
                <div className="flex items-center gap-1">
                    <span className="size-1 animate-bounce rounded-full bg-muted-foreground/60 [animation-delay:-0.3s]" />
                    <span className="size-1 animate-bounce rounded-full bg-muted-foreground/60 [animation-delay:-0.15s]" />
                    <span className="size-1 animate-bounce rounded-full bg-muted-foreground/60" />
                </div>
            </div>
        </div>
    );
}
