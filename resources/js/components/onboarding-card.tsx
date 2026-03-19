import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { CheckCircle2, Circle, X } from 'lucide-react';
import { useState } from 'react';

const DISMISSED_KEY = 'onboarding_card_dismissed';

function isDismissed(userId: number): boolean {
    try {
        return localStorage.getItem(`${DISMISSED_KEY}_${userId}`) === 'true';
    } catch {
        return false;
    }
}

function dismiss(userId: number): void {
    try {
        localStorage.setItem(`${DISMISSED_KEY}_${userId}`, 'true');
    } catch {
        // ignore
    }
}

export function OnboardingCard() {
    const { auth, onboarding } = usePage<SharedData>().props;
    const user = auth.user;
    const [hidden, setHidden] = useState(() => (user ? isDismissed(user.id) : false));

    if (!onboarding?.inProgress || onboarding.steps.length === 0 || !user) {
        return null;
    }

    if (hidden) return null;

    const completedCount = onboarding.steps.filter((s) => s.complete).length;
    const total = onboarding.steps.length;
    const progress = Math.round(onboarding.percentageCompleted);

    const handleDismiss = () => {
        dismiss(user.id);
        setHidden(true);
    };

    return (
        <div className="rounded-xl border bg-card p-5">
            <div className="mb-3 flex items-start justify-between gap-2">
                <div>
                    <h3 className="font-semibold">Get started</h3>
                    <p className="mt-0.5 text-sm text-muted-foreground">
                        {completedCount} of {total} steps completed
                    </p>
                </div>
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-7 shrink-0 text-muted-foreground"
                    onClick={handleDismiss}
                    aria-label="Dismiss"
                >
                    <X className="size-4" />
                </Button>
            </div>

            <div className="mb-4 h-1.5 w-full rounded-full bg-muted">
                <div
                    className="h-1.5 rounded-full bg-primary transition-all duration-500"
                    style={{ width: `${progress}%` }}
                />
            </div>

            <ul className="space-y-2">
                {onboarding.steps.map((step) => (
                    <li
                        key={step.title}
                        className="flex items-center justify-between gap-3 text-sm"
                    >
                        <div className="flex items-center gap-2.5">
                            {step.complete ? (
                                <CheckCircle2 className="size-4 shrink-0 text-emerald-500 dark:text-emerald-400" />
                            ) : (
                                <Circle className="size-4 shrink-0 text-muted-foreground" />
                            )}
                            <span
                                className={
                                    step.complete
                                        ? 'text-muted-foreground line-through'
                                        : 'text-foreground'
                                }
                            >
                                {step.title}
                            </span>
                        </div>
                        {!step.complete && (
                            <Link
                                href={step.link}
                                className="text-xs text-primary underline underline-offset-2 hover:opacity-80"
                            >
                                {step.cta}
                            </Link>
                        )}
                    </li>
                ))}
            </ul>

            {onboarding.nextStep && (
                <Button asChild className="mt-4 w-full" size="sm">
                    <Link href={onboarding.nextStep.link}>
                        Next: {onboarding.nextStep.title} →
                    </Link>
                </Button>
            )}

            <button
                onClick={handleDismiss}
                className="mt-4 block w-full text-center text-xs text-muted-foreground underline-offset-2 hover:text-foreground hover:underline"
            >
                I'll do this later
            </button>
        </div>
    );
}
