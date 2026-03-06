import * as React from 'react';
import { BotIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { ThinkingIndicator } from './thinking-indicator';
import { MarkdownResponse } from './markdown-response';
import { ToolCallCard } from './tool-call-card';
import type { ToolCall } from './assistant-runtime-provider';

export interface AiResponseCardProps {
    content: string;
    isStreaming?: boolean;
    toolCalls?: ToolCall[];
    avatarUrl?: string;
    assistantName?: string;
    className?: string;
}

/**
 * Renders a complete AI assistant response bubble with avatar,
 * markdown content, optional streaming indicator, and tool call cards.
 */
export function AiResponseCard({
    content,
    isStreaming = false,
    toolCalls,
    avatarUrl,
    assistantName = 'Assistant',
    className,
}: AiResponseCardProps) {
    return (
        <div className={cn('flex items-start gap-3', className)}>
            <Avatar className="size-8 shrink-0 mt-0.5">
                {avatarUrl ? (
                    <img src={avatarUrl} alt={assistantName} className="size-full object-cover" />
                ) : (
                    <AvatarFallback className="bg-primary/10 text-primary">
                        <BotIcon className="size-4" />
                    </AvatarFallback>
                )}
            </Avatar>

            <div className="flex-1 min-w-0 space-y-2">
                <p className="text-xs font-semibold text-muted-foreground">{assistantName}</p>

                {/* Tool calls */}
                {toolCalls && toolCalls.length > 0 && (
                    <div className="space-y-1.5">
                        {toolCalls.map((tc) => (
                            <ToolCallCard key={tc.id} toolCall={tc} />
                        ))}
                    </div>
                )}

                {/* Content or thinking indicator */}
                {isStreaming && !content ? (
                    <ThinkingIndicator variant="dots" label="Thinking…" />
                ) : (
                    <div className="relative">
                        <MarkdownResponse content={content} />
                        {isStreaming && (
                            <span
                                aria-hidden
                                className="ml-0.5 inline-block h-4 w-0.5 animate-pulse rounded-full bg-current align-middle"
                            />
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}
