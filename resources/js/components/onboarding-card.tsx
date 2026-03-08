import { Button } from '@/components/ui/button';
import { edit as editProfile } from '@/routes/user-profile';
import { send } from '@/routes/verification';
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
    const { auth } = usePage<SharedData>().props;
    const user = auth.user;
    const [hidden, setHidden] = useState(() => isDismissed(user.id));

    const checklist = [
        { label: 'Create your account', done: true },
        {
            label: 'Verify your email address',
            done: user.email_verified_at !== null,
            action:
                user.email_verified_at === null ? (
                    <Link
                        href={send()}
                        as="button"
                        className="text-xs text-primary underline underline-offset-2 hover:opacity-80"
                    >
                        Resend verification
                    </Link>
                ) : null,
        },
        {
            label: 'Complete your profile',
            done: !!(user.name && user.avatar_profile),
            action: !user.avatar_profile ? (
                <Link
                    href={editProfile()}
                    className="text-xs text-primary underline underline-offset-2 hover:opacity-80"
                >
                    Edit profile
                </Link>
            ) : null,
        },
    ];

    const completedCount = checklist.filter((i) => i.done).length;
    const allDone = completedCount === checklist.length;
    const progress = (completedCount / checklist.length) * 100;

    if (hidden || allDone) return null;

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
                        {completedCount} of {checklist.length} steps completed
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
                {checklist.map((item) => (
                    <li
                        key={item.label}
                        className="flex items-center justify-between gap-3 text-sm"
                    >
                        <div className="flex items-center gap-2.5">
                            {item.done ? (
                                <CheckCircle2 className="size-4 shrink-0 text-emerald-500 dark:text-emerald-400" />
                            ) : (
                                <Circle className="size-4 shrink-0 text-muted-foreground" />
                            )}
                            <span
                                className={
                                    item.done
                                        ? 'text-muted-foreground line-through'
                                        : 'text-foreground'
                                }
                            >
                                {item.label}
                            </span>
                        </div>
                        {!item.done && item.action}
                    </li>
                ))}
            </ul>

            <button
                onClick={handleDismiss}
                className="mt-4 text-xs text-muted-foreground underline-offset-2 hover:text-foreground hover:underline"
            >
                I'll do this later
            </button>
        </div>
    );
}
