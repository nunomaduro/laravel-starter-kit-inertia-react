import { login } from '@/routes';
import { Link } from '@inertiajs/react';
import { Wand2 } from 'lucide-react';

export function HeroSection() {
    return (
        <section className="mx-auto flex w-full max-w-5xl flex-col px-6 pt-24 pb-20">
            <span className="mb-6 font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// STARTER KIT</span>
            <h1 className="font-mono text-4xl font-bold tracking-tight sm:text-5xl" style={{ letterSpacing: '-0.03em' }}>
                Build AI-native corporate apps
                <br />
                <span className="text-primary">in minutes, not months</span>
            </h1>
            <p className="mt-5 max-w-xl text-base leading-relaxed text-muted-foreground">
                Skip 3 months of infrastructure. Describe your app, select modules, ship to production — powered by 70+ packages and an AI assistant that knows your domain.
            </p>
            <div className="mt-8 flex flex-wrap items-center gap-3">
                <Link
                    href="/wizard"
                    data-pan="welcome-hero-wizard"
                    className="rounded-md bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground transition-colors duration-100 hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <Wand2 className="mr-2 inline h-4 w-4" />
                    Launch the Wizard
                </Link>
                <Link
                    href={login()}
                    data-pan="welcome-log-in"
                    className="rounded-md border border-border px-6 py-2.5 text-sm font-medium transition-colors duration-100 hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    Log in
                </Link>
            </div>
            <div className="mt-10 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-xs text-muted-foreground">
                <span>Laravel 13</span>
                <span className="text-border">·</span>
                <span>Inertia v2</span>
                <span className="text-border">·</span>
                <span>React 19</span>
                <span className="text-border">·</span>
                <span>Tailwind CSS v4</span>
                <span className="text-border">·</span>
                <span>Filament v5</span>
                <span className="text-border">·</span>
                <span>Laravel AI SDK</span>
                <span className="text-border">·</span>
                <span>TypeScript</span>
            </div>
        </section>
    );
}
