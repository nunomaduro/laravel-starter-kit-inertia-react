import { Button } from '@/components/ui/button';

export interface HeroBlockProps {
    title: string;
    subtitle: string;
    primaryCtaLabel: string;
    primaryCtaHref: string;
    secondaryCtaLabel?: string;
    secondaryCtaHref?: string;
}

export function HeroBlock({
    title,
    subtitle,
    primaryCtaLabel,
    primaryCtaHref,
    secondaryCtaLabel,
    secondaryCtaHref,
}: HeroBlockProps) {
    return (
        <section className="container flex flex-col items-center gap-6 py-16 text-center md:py-24">
            <h1 className="text-4xl font-bold tracking-tight md:text-5xl lg:text-6xl">
                {title}
            </h1>
            <p className="max-w-[600px] text-lg text-muted-foreground">
                {subtitle}
            </p>
            <div className="flex flex-wrap items-center justify-center gap-4">
                <Button asChild size="lg">
                    <a href={primaryCtaHref}>{primaryCtaLabel}</a>
                </Button>
                {secondaryCtaLabel && secondaryCtaHref && (
                    <Button asChild variant="outline" size="lg">
                        <a href={secondaryCtaHref}>{secondaryCtaLabel}</a>
                    </Button>
                )}
            </div>
        </section>
    );
}
