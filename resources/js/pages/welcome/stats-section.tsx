export function StatsSection() {
    return (
        <section className="border-y border-border bg-muted/40 py-10" data-pan="welcome-stats">
            <div className="mx-auto flex max-w-3xl flex-wrap items-center justify-between gap-8 px-6">
                {[
                    { value: '13', label: 'Modules' },
                    { value: '59', label: 'Models' },
                    { value: '670+', label: 'Tests' },
                    { value: '286', label: 'UI Components' },
                    { value: '92', label: 'Pages' },
                    { value: '100+', label: 'Packages' },
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
