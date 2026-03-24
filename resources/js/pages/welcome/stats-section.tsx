export function StatsSection() {
    return (
        <section className="border-y border-border bg-muted/40 py-10" data-pan="welcome-stats">
            <div className="mx-auto flex max-w-3xl flex-wrap items-center justify-between gap-8 px-6">
                {[
                    { value: '70+', label: 'Packages' },
                    { value: '30+', label: 'Models' },
                    { value: '18', label: 'Files per module' },
                    { value: '155+', label: 'UI Components' },
                ].map((stat) => (
                    <div key={stat.label}>
                        <div className="font-mono text-2xl font-bold tracking-tight">{stat.value}</div>
                        <div className="text-xs text-muted-foreground">{stat.label}</div>
                    </div>
                ))}
            </div>
        </section>
    );
}
