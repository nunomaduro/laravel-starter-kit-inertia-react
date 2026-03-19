import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController';
import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle2, Circle, LoaderCircle } from 'lucide-react';

interface OnboardingStep {
    title: string;
    cta: string;
    link: string;
    complete: boolean;
}

interface OnboardingProps {
    status?: string;
    alreadyCompleted?: boolean;
    steps: OnboardingStep[];
    inProgress: boolean;
    percentageCompleted: number;
    nextStep: { title: string; link: string; cta: string } | null;
}

export default function OnboardingShow({
    status,
    alreadyCompleted,
    steps,
    inProgress,
    percentageCompleted,
    nextStep,
}: OnboardingProps) {
    const name = usePage<SharedData>().props.name;

    const progress = Math.round(percentageCompleted);
    const showGoToDashboard =
        !nextStep || nextStep.title === 'Get started' || steps.every((s) => s.complete);

    return (
        <AppSidebarLayout>
            <Head
                title={alreadyCompleted ? 'Review onboarding' : 'Get started'}
            />
            <div className="flex h-full flex-1 flex-col items-center justify-center gap-8 p-4">
                <div className="w-full max-w-md space-y-6">
                    <div className="text-center">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Welcome to {name}
                        </h1>
                        <p className="mt-2 text-muted-foreground">
                            {alreadyCompleted
                                ? 'Review or run through onboarding again.'
                                : "You're almost ready. Complete the steps below to get started."}
                        </p>
                    </div>

                    {alreadyCompleted && (
                        <div className="rounded-md bg-muted p-3 text-sm text-muted-foreground">
                            You&apos;ve already completed onboarding. You can
                            run through it again below.
                        </div>
                    )}

                    {status && (
                        <div className="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">
                            {status}
                        </div>
                    )}

                    <div className="h-1.5 w-full rounded-full bg-muted">
                        <div
                            className="h-1.5 rounded-full bg-primary transition-all duration-500"
                            style={{ width: `${progress}%` }}
                        />
                    </div>

                    <div className="rounded-xl border bg-card p-6">
                        <h2 className="mb-3 text-sm font-medium text-muted-foreground">
                            Getting started checklist
                        </h2>
                        <ul className="space-y-2">
                            {steps.map((step) => (
                                <li
                                    key={step.title}
                                    className="flex items-center gap-3 text-sm"
                                >
                                    {step.complete ? (
                                        <CheckCircle2 className="size-4 shrink-0 text-emerald-500 dark:text-emerald-400" />
                                    ) : (
                                        <Circle className="size-4 shrink-0 text-muted-foreground" />
                                    )}
                                    <span
                                        className={
                                            step.complete
                                                ? 'text-foreground'
                                                : 'text-muted-foreground'
                                        }
                                    >
                                        {step.title}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {showGoToDashboard ? (
                        <Form
                            {...OnboardingController.store.form()}
                            className="flex flex-col gap-4"
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full"
                                    data-pan="onboarding-get-started"
                                >
                                    {processing ? (
                                        <LoaderCircle className="size-4 animate-spin" />
                                    ) : (
                                        'Go to Dashboard →'
                                    )}
                                </Button>
                            )}
                        </Form>
                    ) : (
                        <Button asChild className="w-full">
                            <Link href={nextStep.link}>
                                Next: {nextStep.title} →
                            </Link>
                        </Button>
                    )}
                </div>
            </div>
        </AppSidebarLayout>
    );
}
