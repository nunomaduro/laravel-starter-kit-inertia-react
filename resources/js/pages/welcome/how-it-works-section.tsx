import { MessageSquare, Rocket, Wand2 } from 'lucide-react';

export function HowItWorksSection() {
    return (
        <section className="mx-auto w-full max-w-5xl px-6 py-16">
            <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// HOW IT WORKS</span>
            <h2 className="mb-10 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>From idea to production in three steps</h2>
            <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-3">
                {[
                    { step: '01', icon: MessageSquare, title: 'Describe', description: 'Tell the AI wizard what you want to build — HR system, CRM, fleet tracker. Plain English.' },
                    { step: '02', icon: Wand2, title: 'Generate', description: 'The factory analyzes your description, selects modules, and scaffolds models, controllers, pages, and tests.' },
                    { step: '03', icon: Rocket, title: 'Ship', description: 'Multi-tenancy, billing, auth, and AI chat are already wired. Deploy your production-ready app.' },
                ].map((item) => (
                    <div key={item.step} className="bg-card p-6" data-pan={`welcome-step-${item.step}`}>
                        <span className="font-mono text-xs text-muted-foreground">{item.step}</span>
                        <item.icon className="mt-3 mb-3 h-5 w-5 text-primary" />
                        <h3 className="font-mono text-sm font-semibold tracking-tight">{item.title}</h3>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{item.description}</p>
                    </div>
                ))}
            </div>
        </section>
    );
}
