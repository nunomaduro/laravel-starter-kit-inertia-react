import { Button } from '@/components/ui/button';
import { AnimatePresence, motion } from 'framer-motion';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

export type TourStep = {
    title: string;
    description: string;
    target?: string; // CSS selector for spotlight (optional)
    action?: string; // CTA text like "Try it →"
    href?: string; // Link for the CTA
};

type GuidedTourProps = {
    steps: TourStep[];
    storageKey?: string;
    onComplete?: () => void;
};

export function GuidedTour({ steps, storageKey = 'showcase_tour_completed', onComplete }: GuidedTourProps) {
    const [currentStep, setCurrentStep] = useState(0);
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const completed = localStorage.getItem(storageKey);
        if (!completed) {
            // Small delay so the page renders first
            const timer = setTimeout(() => setIsVisible(true), 500);
            return () => clearTimeout(timer);
        }
    }, [storageKey]);

    const dismiss = useCallback(() => {
        setIsVisible(false);
        localStorage.setItem(storageKey, 'true');
        onComplete?.();
    }, [storageKey, onComplete]);

    const next = useCallback(() => {
        if (currentStep < steps.length - 1) {
            setCurrentStep((s) => s + 1);
        } else {
            dismiss();
        }
    }, [currentStep, steps.length, dismiss]);

    const prev = useCallback(() => {
        if (currentStep > 0) {
            setCurrentStep((s) => s - 1);
        }
    }, [currentStep]);

    useEffect(() => {
        function handleKeyDown(e: KeyboardEvent) {
            if (!isVisible) return;
            if (e.key === 'Escape') dismiss();
            if (e.key === 'ArrowRight') next();
            if (e.key === 'ArrowLeft') prev();
        }
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isVisible, dismiss, next, prev]);

    if (!isVisible || steps.length === 0) return null;

    const step = steps[currentStep];
    const isLast = currentStep === steps.length - 1;

    return (
        <AnimatePresence>
            {isVisible && (
                <>
                    {/* Backdrop */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
                        onClick={dismiss}
                    />

                    {/* Tour card */}
                    <motion.div
                        initial={{ opacity: 0, y: 20, scale: 0.95 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 20, scale: 0.95 }}
                        transition={{ type: 'spring', damping: 25, stiffness: 300 }}
                        className="fixed bottom-8 left-1/2 z-50 w-full max-w-lg -translate-x-1/2 rounded-xl border bg-card p-6 shadow-2xl"
                    >
                        {/* Close button */}
                        <button
                            onClick={dismiss}
                            className="absolute right-3 top-3 rounded-md p-1 text-muted-foreground hover:text-foreground"
                        >
                            <X className="h-4 w-4" />
                        </button>

                        {/* Progress dots */}
                        <div className="mb-4 flex gap-1.5">
                            {steps.map((_, i) => (
                                <div
                                    key={i}
                                    className={`h-1.5 rounded-full transition-all ${
                                        i === currentStep
                                            ? 'w-6 bg-primary'
                                            : i < currentStep
                                              ? 'w-1.5 bg-primary/40'
                                              : 'w-1.5 bg-muted'
                                    }`}
                                />
                            ))}
                        </div>

                        {/* Content */}
                        <motion.div key={currentStep} initial={{ opacity: 0, x: 10 }} animate={{ opacity: 1, x: 0 }} transition={{ duration: 0.2 }}>
                            <h3 className="text-lg font-semibold">{step.title}</h3>
                            <p className="mt-2 text-sm text-muted-foreground leading-relaxed">{step.description}</p>
                        </motion.div>

                        {/* Actions */}
                        <div className="mt-6 flex items-center justify-between">
                            <div className="flex gap-2">
                                {currentStep > 0 && (
                                    <Button variant="ghost" size="sm" onClick={prev}>
                                        <ChevronLeft className="mr-1 h-4 w-4" />
                                        Back
                                    </Button>
                                )}
                            </div>
                            <div className="flex gap-2">
                                <Button variant="ghost" size="sm" onClick={dismiss}>
                                    Skip tour
                                </Button>
                                {step.href ? (
                                    <a href={step.href}>
                                        <Button size="sm">
                                            {step.action ?? 'Try it'}
                                            <ChevronRight className="ml-1 h-4 w-4" />
                                        </Button>
                                    </a>
                                ) : (
                                    <Button size="sm" onClick={next}>
                                        {isLast ? 'Get started' : 'Next'}
                                        <ChevronRight className="ml-1 h-4 w-4" />
                                    </Button>
                                )}
                            </div>
                        </div>

                        {/* Step counter */}
                        <p className="mt-3 text-center text-xs text-muted-foreground">
                            {currentStep + 1} of {steps.length}
                        </p>
                    </motion.div>
                </>
            )}
        </AnimatePresence>
    );
}
