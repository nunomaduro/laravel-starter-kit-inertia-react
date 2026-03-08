import { UserIcon } from 'lucide-react';
import * as React from 'react';

import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { cn } from '@/lib/utils';
import { AiResponseCard } from './ai-response-card';
import { useAssistantRuntime } from './assistant-runtime-provider';
import { PromptInput } from './prompt-input';

export interface AssistantThreadProps {
    /** Placeholder text for the input. */
    placeholder?: string;
    /** Name displayed for assistant messages. */
    assistantName?: string;
    /** Optional welcome message shown when thread is empty. */
    welcomeMessage?: string;
    className?: string;
}

/**
 * Full chat thread UI. Must be a descendant of `AssistantRuntimeProvider`.
 * Renders the message history, a scrollable viewport, and the prompt input.
 */
export function AssistantThread({
    placeholder = 'Ask anything…',
    assistantName = 'Assistant',
    welcomeMessage,
    className,
}: AssistantThreadProps) {
    const { messages, isLoading, append, stop } = useAssistantRuntime();
    const bottomRef = React.useRef<HTMLDivElement>(null);

    React.useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    return (
        <div className={cn('flex h-full flex-col', className)}>
            {/* Message list */}
            <div className="flex-1 overflow-y-auto px-4">
                <div className="space-y-6 py-4">
                    {messages.length === 0 && welcomeMessage && (
                        <p className="py-8 text-center text-sm text-muted-foreground">
                            {welcomeMessage}
                        </p>
                    )}

                    {messages.map((message) => {
                        if (message.role === 'user') {
                            return (
                                <div
                                    key={message.id}
                                    className="flex items-start justify-end gap-3"
                                >
                                    <div className="max-w-[80%] rounded-2xl rounded-tr-sm bg-primary px-4 py-2.5 text-sm text-primary-foreground">
                                        <p className="break-words whitespace-pre-wrap">
                                            {message.content}
                                        </p>
                                    </div>
                                    <Avatar className="mt-0.5 size-8 shrink-0">
                                        <AvatarFallback className="bg-secondary/20 text-secondary-foreground">
                                            <UserIcon className="size-4" />
                                        </AvatarFallback>
                                    </Avatar>
                                </div>
                            );
                        }

                        return (
                            <AiResponseCard
                                key={message.id}
                                content={message.content}
                                isStreaming={message.isStreaming}
                                toolCalls={message.toolCalls}
                                assistantName={assistantName}
                            />
                        );
                    })}

                    <div ref={bottomRef} />
                </div>
            </div>

            {/* Input */}
            <div className="border-t bg-background px-4 py-3">
                <PromptInput
                    placeholder={placeholder}
                    isLoading={isLoading}
                    onSubmit={append}
                    onStop={stop}
                />
            </div>
        </div>
    );
}
