import { Link } from '@inertiajs/react';
import { CheckIcon, ChevronDownIcon, ChevronUpIcon } from 'lucide-react';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Progress } from '@/components/ui/progress';
import { cn } from '@/lib/utils';

interface OnboardingStep {
    id: string;
    label: string;
    completed: boolean;
    href?: string;
}

interface OnboardingChecklistProps {
    steps: OnboardingStep[];
    title?: string;
    variant?: 'floating' | 'inline';
    className?: string;
}

function OnboardingChecklist({
    steps,
    title = 'Get Started',
    variant = 'inline',
    className,
}: OnboardingChecklistProps) {
    const completed = steps.filter((s) => s.completed).length;
    const total = steps.length;
    const allDone = completed === total;
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
    const [collapsed, setCollapsed] = React.useState(allDone);

    React.useEffect(() => {
        if (allDone) setCollapsed(true);
    }, [allDone]);

    if (allDone && collapsed) {
        return (
            <div
                className={cn(
                    'flex items-center gap-2 rounded-lg border border-border bg-card p-3 text-sm text-muted-foreground',
                    variant === 'floating' && 'shadow-md',
                    className,
                )}
            >
                <CheckIcon className="size-4 text-green-500" />
                <span>Setup complete!</span>
            </div>
        );
    }

    const content = (
        <>
            <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-semibold">{title}</CardTitle>
                <div className="flex items-center gap-2">
                    <span className="text-xs text-muted-foreground">
                        {completed}/{total}
                    </span>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="size-6"
                        onClick={() => setCollapsed((v) => !v)}
                        aria-label={
                            collapsed
                                ? 'Expand checklist'
                                : 'Collapse checklist'
                        }
                    >
                        {collapsed ? (
                            <ChevronDownIcon className="size-3" />
                        ) : (
                            <ChevronUpIcon className="size-3" />
                        )}
                    </Button>
                </div>
            </div>
            <Progress value={percentage} className="h-1.5" />
            {!collapsed && (
                <ul className="mt-3 space-y-2">
                    {steps.map((step) => (
                        <li
                            key={step.id}
                            className="flex items-center gap-2.5 text-sm"
                        >
                            <Checkbox
                                checked={step.completed}
                                aria-label={step.label}
                                className="pointer-events-none"
                            />
                            {step.href && !step.completed ? (
                                <Link
                                    href={step.href}
                                    className={cn(
                                        'hover:underline',
                                        step.completed
                                            ? 'text-muted-foreground line-through'
                                            : '',
                                    )}
                                >
                                    {step.label}
                                </Link>
                            ) : (
                                <span
                                    className={cn(
                                        step.completed
                                            ? 'text-muted-foreground line-through'
                                            : '',
                                    )}
                                >
                                    {step.label}
                                </span>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </>
    );

    return (
        <Card
            className={cn(
                'p-4',
                variant === 'floating' &&
                    'fixed right-4 bottom-4 z-50 w-72 shadow-lg',
                className,
            )}
        >
            <CardHeader className="p-0 pb-2">
                <div className="space-y-2">{content}</div>
            </CardHeader>
            {!collapsed && <CardContent className="p-0" />}
        </Card>
    );
}

export { OnboardingChecklist };
export type { OnboardingChecklistProps, OnboardingStep };
