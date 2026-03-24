import { Link } from '@inertiajs/react';
import { Terminal, Wand2 } from 'lucide-react';

export function CtaSection() {
    return (
        <section className="mx-auto w-full max-w-5xl px-6 py-16">
            <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// GET STARTED</span>
            <h2 className="font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Ready to build your next app?</h2>
            <p className="mt-2 text-sm text-muted-foreground">Describe your idea and let the AI Factory do the rest.</p>
            <div className="mt-8 flex flex-wrap items-center gap-4">
                <Link
                    href="/wizard"
                    data-pan="welcome-cta-wizard"
                    className="rounded-md bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground transition-colors duration-100 hover:bg-primary/90"
                >
                    <Wand2 className="mr-2 inline h-4 w-4" />
                    Open the Wizard
                </Link>
                <div className="rounded-md border border-border bg-muted px-4 py-2.5 font-mono text-xs text-muted-foreground" data-pan="welcome-cta-cli">
                    <Terminal className="mr-2 inline h-3.5 w-3.5" />
                    php artisan factory:create &quot;An HR system for a logistics company&quot;
                </div>
            </div>
        </section>
    );
}
