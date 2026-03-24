import { Link } from '@inertiajs/react';
import { Bot, Terminal, Wand2 } from 'lucide-react';

export function DifferentiatorsSection() {
    return (
        <section className="mx-auto w-full max-w-5xl px-6 py-16">
            <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// WHY THIS</span>
            <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>What makes this different</h2>
            <p className="mb-10 text-sm text-muted-foreground">Not just a starter kit — an AI-powered app factory</p>
            <div className="grid gap-4 sm:grid-cols-3">
                <Link href="/chat" className="rounded-lg border border-border bg-card p-6 transition-colors duration-100 hover:bg-accent" data-pan="welcome-diff-ai">
                    <Bot className="mb-3 h-5 w-5 text-primary" />
                    <h3 className="font-mono text-sm font-semibold tracking-tight">AI Assistant</h3>
                    <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                        A built-in AI chat that understands your domain. Ask questions about your data, get help with code, or guide your users. Multi-provider with memory and RAG.
                    </p>
                </Link>
                <Link href="/showcase" className="rounded-lg border border-border bg-card p-6 transition-colors duration-100 hover:bg-accent" data-pan="welcome-diff-modules">
                    <Wand2 className="mb-3 h-5 w-5 text-primary" />
                    <h3 className="font-mono text-sm font-semibold tracking-tight">Module System</h3>
                    <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                        Pre-built domain modules (HR, CRM, Fleet) with full CRUD, admin panel, Inertia pages, and tests. Cross-module AI intelligence included.
                    </p>
                </Link>
                <div className="rounded-lg border border-border bg-card p-6" data-pan="welcome-diff-scaffold">
                    <Terminal className="mb-3 h-5 w-5 text-primary" />
                    <h3 className="font-mono text-sm font-semibold tracking-tight">One-Command Scaffolding</h3>
                    <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                        Run <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">make:module</code> for 18 files or{' '}
                        <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">factory:create</code> with AI analysis. From description to app in minutes.
                    </p>
                </div>
            </div>
        </section>
    );
}
