import * as React from 'react';
import { CheckIcon, ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';

export interface FormWizardStep {
    id: string;
    title: string;
    description?: string;
    content: React.ReactNode;
    onValidate?: () => boolean | Promise<boolean>;
}

export interface FormWizardProps {
    steps: FormWizardStep[];
    onComplete: () => void;
    onCancel?: () => void;
    completeLabel?: string;
    cancelLabel?: string;
    className?: string;
    showProgress?: boolean;
}

function FormWizard({
    steps,
    onComplete,
    onCancel,
    completeLabel = 'Finish',
    cancelLabel = 'Cancel',
    className,
    showProgress = true,
}: FormWizardProps) {
    const [currentIndex, setCurrentIndex] = React.useState(0);
    const [completedSteps, setCompletedSteps] = React.useState<Set<number>>(new Set());
    const [isValidating, setIsValidating] = React.useState(false);

    const currentStep = steps[currentIndex];
    const isFirst = currentIndex === 0;
    const isLast = currentIndex === steps.length - 1;
    const progressPercent = ((currentIndex + 1) / steps.length) * 100;

    const handleNext = async () => {
        if (currentStep.onValidate) {
            setIsValidating(true);
            const valid = await currentStep.onValidate();
            setIsValidating(false);
            if (!valid) return;
        }
        setCompletedSteps((prev) => new Set(prev).add(currentIndex));
        if (isLast) {
            onComplete();
        } else {
            setCurrentIndex((i) => i + 1);
        }
    };

    const handleBack = () => {
        if (!isFirst) setCurrentIndex((i) => i - 1);
    };

    const handleStepClick = (index: number) => {
        if (index < currentIndex || completedSteps.has(index)) {
            setCurrentIndex(index);
        }
    };

    return (
        <Card data-slot="form-wizard" className={cn('w-full', className)}>
            <CardHeader className="pb-3">
                <div className="flex items-center gap-3">
                    {steps.map((step, i) => (
                        <React.Fragment key={step.id}>
                            <button
                                type="button"
                                onClick={() => handleStepClick(i)}
                                className={cn(
                                    'flex size-7 shrink-0 items-center justify-center rounded-full border-2 text-xs font-semibold transition-colors',
                                    i === currentIndex
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : completedSteps.has(i)
                                          ? 'border-success bg-success text-white cursor-pointer'
                                          : i < currentIndex
                                            ? 'border-muted-foreground/50 text-muted-foreground cursor-pointer hover:border-primary'
                                            : 'border-muted text-muted-foreground',
                                )}
                                aria-label={`Step ${i + 1}: ${step.title}`}
                            >
                                {completedSteps.has(i) ? (
                                    <CheckIcon className="size-3.5" />
                                ) : (
                                    i + 1
                                )}
                            </button>
                            {i < steps.length - 1 && (
                                <div
                                    className={cn(
                                        'h-0.5 flex-1 rounded-full',
                                        i < currentIndex ? 'bg-primary' : 'bg-muted',
                                    )}
                                />
                            )}
                        </React.Fragment>
                    ))}
                </div>
                {showProgress && (
                    <Progress value={progressPercent} className="h-1 mt-3" />
                )}
                <CardTitle className="mt-2 text-base">{currentStep.title}</CardTitle>
                {currentStep.description && (
                    <p className="text-sm text-muted-foreground">{currentStep.description}</p>
                )}
            </CardHeader>
            <CardContent>{currentStep.content}</CardContent>
            <CardFooter className="flex justify-between gap-2 border-t pt-4">
                <div>
                    {onCancel && isFirst && (
                        <Button variant="ghost" size="sm" onClick={onCancel}>
                            {cancelLabel}
                        </Button>
                    )}
                    {!isFirst && (
                        <Button variant="outline" size="sm" onClick={handleBack}>
                            <ChevronLeftIcon className="mr-1 size-4" />
                            Back
                        </Button>
                    )}
                </div>
                <Button size="sm" onClick={handleNext} disabled={isValidating}>
                    {isLast ? completeLabel : (
                        <>
                            Next
                            <ChevronRightIcon className="ml-1 size-4" />
                        </>
                    )}
                </Button>
            </CardFooter>
        </Card>
    );
}

export { FormWizard };
