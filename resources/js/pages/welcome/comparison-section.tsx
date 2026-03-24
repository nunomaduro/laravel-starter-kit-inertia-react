export function ComparisonSection() {
    return (
        <section className="mx-auto w-full max-w-5xl px-6 py-16">
            <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// COMPARISON</span>
            <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Skip 3-6 months of setup</h2>
            <p className="mb-10 text-sm text-muted-foreground">What you get out of the box vs building from scratch</p>
            <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-3">
                {[
                    { feature: 'Multi-tenant auth + RBAC', scratch: '3-4 weeks', kit: 'Day 1' },
                    { feature: 'Billing (3 gateways + seats)', scratch: '2-3 weeks', kit: 'Day 1' },
                    { feature: 'Admin panel (Filament v5)', scratch: '2-4 weeks', kit: 'Day 1' },
                    { feature: 'AI chat + MCP server', scratch: '2-3 weeks', kit: 'Day 1' },
                    { feature: 'Real-time WebSockets', scratch: '1-2 weeks', kit: 'Day 1' },
                    { feature: '13 domain modules', scratch: '3-6 months', kit: 'Day 1' },
                    { feature: 'Full-text search (Typesense)', scratch: '1-2 weeks', kit: 'Day 1' },
                    { feature: 'Monitoring (Horizon + Pulse)', scratch: '1-2 weeks', kit: 'Day 1' },
                    { feature: '670+ tests with Pest', scratch: '2-3 weeks', kit: 'Day 1' },
                ].map((row) => (
                    <div key={row.feature} className="flex items-center justify-between bg-card p-4">
                        <span className="text-sm">{row.feature}</span>
                        <div className="flex items-center gap-3 font-mono text-xs">
                            <span className="text-muted-foreground line-through">{row.scratch}</span>
                            <span className="font-semibold text-primary">{row.kit}</span>
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}
