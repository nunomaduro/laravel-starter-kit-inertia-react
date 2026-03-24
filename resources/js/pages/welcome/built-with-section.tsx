export function BuiltWithSection() {
    return (
        <section className="mx-auto w-full max-w-5xl px-6 py-16">
            <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// BUILT WITH</span>
            <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Production-grade stack</h2>
            <p className="mb-10 text-sm text-muted-foreground">Every dependency is battle-tested and actively maintained</p>
            <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-2 lg:grid-cols-4">
                {[
                    { name: 'Laravel 13', detail: 'PHP 8.4, strict types' },
                    { name: 'React 19', detail: 'Inertia v2, TypeScript' },
                    { name: 'Filament v5', detail: 'Admin panel, SDUI' },
                    { name: 'Tailwind v4', detail: '286 shadcn components' },
                    { name: 'Laravel AI SDK', detail: 'Agents, tools, streaming' },
                    { name: 'Pest 4', detail: '670+ tests, full coverage' },
                    { name: 'Horizon + Pulse', detail: 'Queues, monitoring' },
                    { name: 'Scout + Typesense', detail: 'Full-text search' },
                    { name: 'Reverb + Echo', detail: 'Real-time WebSockets' },
                    { name: 'Sanctum + Socialite', detail: 'API tokens, OAuth' },
                    { name: 'Spatie Suite', detail: 'Permissions, backup, health' },
                ].map((tech) => (
                    <div key={tech.name} className="bg-card p-4">
                        <div className="font-mono text-sm font-semibold tracking-tight">{tech.name}</div>
                        <div className="mt-1 text-xs text-muted-foreground">{tech.detail}</div>
                    </div>
                ))}
            </div>
        </section>
    );
}
