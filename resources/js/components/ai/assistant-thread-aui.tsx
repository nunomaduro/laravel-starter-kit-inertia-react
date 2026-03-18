'use client';

import * as React from 'react';
import {
    AssistantRuntimeProvider,
    ComposerPrimitive,
    ThreadPrimitive,
    useLocalRuntime,
} from '@assistant-ui/react';
import { createLaravelChatAdapter } from './laravel-chat-adapter';
import type { LaravelAdapterConfig } from './laravel-chat-adapter';

export interface AssistantThreadAuiProps extends LaravelAdapterConfig {
    placeholder?: string;
    className?: string;
}

/**
 * Minimal thread UI using @assistant-ui/react primitives, wired to the Laravel streaming endpoint.
 * Use inside a dialog or sheet; wrap with AssistantRuntimeProvider when using standalone.
 */
function ThreadContent({ placeholder }: { placeholder?: string }) {
    return (
        <ThreadPrimitive.Root className="flex min-h-0 flex-1 flex-col">
            <ThreadPrimitive.Viewport className="min-h-0 flex-1 overflow-y-auto">
                <ThreadPrimitive.Messages />
                <ThreadPrimitive.ViewportFooter className="sticky bottom-0 border-t bg-background p-2">
                    <ComposerPrimitive.Root className="flex gap-2">
                        <ComposerPrimitive.Input
                            placeholder={placeholder ?? 'Ask anything…'}
                            className="min-h-9 flex-1 rounded-md border bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                        />
                        <ComposerPrimitive.Send className="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90" />
                    </ComposerPrimitive.Root>
                </ThreadPrimitive.ViewportFooter>
            </ThreadPrimitive.Viewport>
        </ThreadPrimitive.Root>
    );
}

/**
 * Assistant thread powered by assistant-ui and the Laravel chat adapter.
 * Renders a full runtime provider and minimal thread (messages + composer).
 */
export function AssistantThreadAui({
    endpoint,
    model,
    headers,
    systemPrompt,
    placeholder,
    className,
}: AssistantThreadAuiProps) {
    const adapter = React.useMemo(
        () =>
            createLaravelChatAdapter({
                endpoint,
                model,
                headers,
                systemPrompt,
            }),
        [endpoint, model, headers, systemPrompt],
    );
    const runtime = useLocalRuntime(adapter);

    return (
        <AssistantRuntimeProvider runtime={runtime}>
            <div className={className}>
                <ThreadContent placeholder={placeholder} />
            </div>
        </AssistantRuntimeProvider>
    );
}
