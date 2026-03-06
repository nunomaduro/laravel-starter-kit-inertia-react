import * as React from 'react';
import {
    CircleIcon,
    CheckCircle2Icon,
    XCircleIcon,
    Loader2Icon,
    PauseCircleIcon,
    AlertCircleIcon,
} from 'lucide-react';

import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';

export type AgentState =
    | 'idle'
    | 'thinking'
    | 'running'
    | 'waiting'
    | 'done'
    | 'error'
    | 'paused';

export interface AgentStep {
    id: string;
    label: string;
    state: AgentState;
    detail?: string;
}

export interface AgentStatusProps {
    /** Agent name or identifier. */
    name?: string;
    /** Overall agent state. */
    state: AgentState;
    /** Optional list of sub-steps / tasks. */
    steps?: AgentStep[];
    /** Status message shown below the agent name. */
    message?: string;
    className?: string;
}

const STATE_ICONS: Record<AgentState, React.ReactNode> = {
    idle: <CircleIcon className="size-3.5 text-muted-foreground" />,
    thinking: <Loader2Icon className="size-3.5 animate-spin text-primary" />,
    running: <Loader2Icon className="size-3.5 animate-spin text-primary" />,
    waiting: <PauseCircleIcon className="size-3.5 text-warning" />,
    done: <CheckCircle2Icon className="size-3.5 text-success" />,
    error: <XCircleIcon className="size-3.5 text-error" />,
    paused: <PauseCircleIcon className="size-3.5 text-muted-foreground" />,
};

const STATE_BADGE: Record<AgentState, { label: string; className: string }> = {
    idle: { label: 'Idle', className: 'bg-muted text-muted-foreground' },
    thinking: { label: 'Thinking', className: 'bg-primary/10 text-primary' },
    running: { label: 'Running', className: 'bg-primary/10 text-primary' },
    waiting: { label: 'Waiting', className: 'bg-warning/10 text-warning' },
    done: { label: 'Done', className: 'bg-success/10 text-success' },
    error: { label: 'Error', className: 'bg-error/10 text-error' },
    paused: { label: 'Paused', className: 'bg-muted text-muted-foreground' },
};

/**
 * Displays the current status of an AI agent with optional step breakdown.
 */
export function AgentStatus({
    name = 'Agent',
    state,
    steps,
    message,
    className,
}: AgentStatusProps) {
    const badge = STATE_BADGE[state];

    return (
        <div
            className={cn('rounded-lg border bg-card p-3 space-y-2', className)}
            aria-label={`${name} status: ${badge.label}`}
        >
            <div className="flex items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                    {STATE_ICONS[state]}
                    <span className="text-sm font-medium">{name}</span>
                </div>
                <Badge className={cn('h-4 px-1.5 text-[10px]', badge.className)}>
                    {badge.label}
                </Badge>
            </div>

            {message && (
                <p className="text-xs text-muted-foreground">{message}</p>
            )}

            {steps && steps.length > 0 && (
                <ul className="space-y-1 pt-1 border-t">
                    {steps.map((step) => (
                        <li key={step.id} className="flex items-start gap-2">
                            <span className="mt-0.5">{STATE_ICONS[step.state]}</span>
                            <div className="flex-1 min-w-0">
                                <p className="text-xs font-medium truncate">{step.label}</p>
                                {step.detail && (
                                    <p className="text-[10px] text-muted-foreground truncate">
                                        {step.detail}
                                    </p>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export function AgentStatusInline({
    state,
    label,
    className,
}: {
    state: AgentState;
    label?: string;
    className?: string;
}) {
    const badge = STATE_BADGE[state];
    return (
        <span className={cn('inline-flex items-center gap-1.5', className)}>
            {STATE_ICONS[state]}
            <span className={cn('text-xs font-medium', badge.className.split(' ')[1])}>
                {label ?? badge.label}
            </span>
        </span>
    );
}
