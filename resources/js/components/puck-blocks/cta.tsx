import { Button } from '@/components/ui/button';

export interface CtaBlockProps {
    heading: string;
    description?: string;
    buttonLabel: string;
    buttonHref: string;
}

export function CtaBlock({
    heading,
    description,
    buttonLabel,
    buttonHref,
}: CtaBlockProps) {
    return (
        <section className="container py-16 md:py-24">
            <div className="flex flex-col items-center gap-6 rounded-xl border bg-muted/50 px-6 py-12 text-center md:px-12">
                <h2 className="text-2xl font-bold tracking-tight md:text-3xl">
                    {heading}
                </h2>
                {description && (
                    <p className="max-w-[500px] text-muted-foreground">
                        {description}
                    </p>
                )}
                <Button asChild size="lg">
                    <a href={buttonHref}>{buttonLabel}</a>
                </Button>
            </div>
        </section>
    );
}
