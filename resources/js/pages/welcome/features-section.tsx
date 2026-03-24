import type { FeatureGroup } from './feature-data';

interface FeaturesSectionProps {
    featureGroups: FeatureGroup[];
}

export function FeaturesSection({ featureGroups }: FeaturesSectionProps) {
    return (
        <section className="mx-auto w-full max-w-5xl space-y-10 px-6 py-16">
            <div>
                <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// FEATURES</span>
                <h2 className="font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>60+ features, all production-ready</h2>
                <p className="mt-2 text-sm text-muted-foreground">Everything you need across 9 domains</p>
            </div>
            {featureGroups.map((group) => (
                <div key={group.label}>
                    <h3 className="mb-4 font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-muted-foreground">{group.label}</h3>
                    <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-2 lg:grid-cols-4">
                        {group.features.map((feature) => (
                            <div key={feature.title} className="bg-card p-4" data-pan={feature.dataPan}>
                                <feature.icon className="mb-2 size-4 text-primary" />
                                <h4 className="font-mono text-xs font-semibold tracking-tight">{feature.title}</h4>
                                <p className="mt-1 text-xs leading-relaxed text-muted-foreground">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            ))}
        </section>
    );
}
