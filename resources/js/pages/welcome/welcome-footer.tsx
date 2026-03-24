import { privacy as legalPrivacy, terms as legalTerms } from '@/routes/legal';
import { Link } from '@inertiajs/react';

export function WelcomeFooter() {
    return (
        <footer className="border-t border-border py-6 text-sm text-muted-foreground">
            <div className="mx-auto max-w-5xl px-6">
                <Link href={legalTerms().url} className="transition-colors duration-100 hover:text-foreground">
                    Terms of Service
                </Link>
                {' · '}
                <Link href={legalPrivacy().url} className="transition-colors duration-100 hover:text-foreground">
                    Privacy Policy
                </Link>
            </div>
        </footer>
    );
}
