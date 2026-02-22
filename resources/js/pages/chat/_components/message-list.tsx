import type { User } from '@/types';
import type { UIMessage } from '@tanstack/ai-client';
import { ArrowDown } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { MessageBubble, StreamingIndicator } from './message-bubble';

export function MessageList({
    messages,
    isLoading,
    user,
}: {
    messages: UIMessage[];
    isLoading: boolean;
    user: User;
}) {
    const scrollRef = useRef<HTMLDivElement>(null);
    const [showScrollButton, setShowScrollButton] = useState(false);

    const isNearBottom = useCallback(() => {
        const el = scrollRef.current;
        if (!el) return true;
        return el.scrollHeight - el.scrollTop - el.clientHeight < 100;
    }, []);

    const scrollToBottom = useCallback(() => {
        const el = scrollRef.current;
        if (el) {
            el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
        }
    }, []);

    useEffect(() => {
        if (isNearBottom()) {
            scrollToBottom();
        }
    }, [messages, isLoading, isNearBottom, scrollToBottom]);

    const handleScroll = useCallback(() => {
        setShowScrollButton(!isNearBottom());
    }, [isNearBottom]);

    return (
        <div className="relative min-h-0 flex-1">
            <div
                ref={scrollRef}
                onScroll={handleScroll}
                className="absolute inset-0 space-y-4 overflow-y-auto px-4 py-4"
            >
                {messages.map((m) => (
                    <MessageBubble key={m.id} message={m} user={user} />
                ))}
                {isLoading && messages[messages.length - 1]?.role !== 'assistant' && (
                    <StreamingIndicator />
                )}
            </div>
            {showScrollButton && (
                <button
                    type="button"
                    onClick={scrollToBottom}
                    className="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full border bg-background p-2 shadow-md transition-opacity hover:bg-accent"
                >
                    <ArrowDown className="size-4" />
                </button>
            )}
        </div>
    );
}
