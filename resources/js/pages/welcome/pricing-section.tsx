import { register } from '@/routes';
import { Link } from '@inertiajs/react';
import { Check } from 'lucide-react';

interface PricingTier {
    name: string;
    price: string;
    period: string;
    description: string;
    features: string[];
    cta: string;
    highlighted: boolean;
}

interface PricingSectionProps {
    tiers: PricingTier[];
}

export function PricingSection({ tiers }: PricingSectionProps) {
    return (
        <section className="mx-auto w-full max-w-5xl px-6 py-16">
            <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// PRICING</span>
            <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Simple pricing</h2>
            <p className="mb-10 text-sm text-muted-foreground">Start building today. Scale when you're ready.</p>
            <div className="grid gap-4 sm:grid-cols-3">
                {tiers.map((tier) => (
                    <div
                        key={tier.name}
                        className={`relative rounded-lg border p-6 ${tier.highlighted ? 'border-primary bg-primary/5' : 'border-border bg-card'}`}
                        data-pan={`welcome-pricing-${tier.name.toLowerCase()}`}
                    >
                        {tier.highlighted && (
                            <span className="absolute -top-3 left-4 rounded-full bg-primary px-3 py-0.5 font-mono text-[11px] font-medium text-primary-foreground">
                                Most popular
                            </span>
                        )}
                        <h3 className="font-mono text-base font-semibold tracking-tight">{tier.name}</h3>
                        <p className="text-sm text-muted-foreground">{tier.description}</p>
                        <div className="mt-4">
                            <span className="font-mono text-3xl font-bold tracking-tight">{tier.price}</span>
                            <span className="text-sm text-muted-foreground">{tier.period}</span>
                        </div>
                        <ul className="mt-6 space-y-2">
                            {tier.features.map((f) => (
                                <li key={f} className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Check className="h-3.5 w-3.5 shrink-0 text-primary" />
                                    {f}
                                </li>
                            ))}
                        </ul>
                        <Link
                            href={tier.name === 'Enterprise' ? '/contact' : register()}
                            data-pan={`welcome-pricing-${tier.name.toLowerCase()}-cta`}
                            className={`mt-6 block rounded-md px-4 py-2 text-center text-sm font-medium transition-colors duration-100 ${
                                tier.highlighted
                                    ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                    : 'border border-border hover:bg-accent'
                            }`}
                        >
                            {tier.cta}
                        </Link>
                    </div>
                ))}
            </div>
        </section>
    );
}
