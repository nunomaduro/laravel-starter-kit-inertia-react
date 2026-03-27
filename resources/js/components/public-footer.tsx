import { Link } from '@inertiajs/react';

export default function PublicFooter() {
    return (
        <footer className="border-t bg-neutral-50 dark:bg-neutral-950">
            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <h3 className="text-sm font-semibold text-foreground">Company</h3>
                        <ul className="mt-4 space-y-2">
                            <li>
                                <Link href="/about" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    About
                                </Link>
                            </li>
                            <li>
                                <Link href="/contact" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    Contact
                                </Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 className="text-sm font-semibold text-foreground">Hosting</h3>
                        <ul className="mt-4 space-y-2">
                            <li>
                                <Link href="/host/register" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    Become a Host
                                </Link>
                            </li>
                            <li>
                                <Link href="/host/resources" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    Host Resources
                                </Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 className="text-sm font-semibold text-foreground">Support</h3>
                        <ul className="mt-4 space-y-2">
                            <li>
                                <Link href="/help" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    Help Center
                                </Link>
                            </li>
                            <li>
                                <Link href="/safety" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    Safety
                                </Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 className="text-sm font-semibold text-foreground">Legal</h3>
                        <ul className="mt-4 space-y-2">
                            <li>
                                <Link href="/privacy" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    Privacy Policy
                                </Link>
                            </li>
                            <li>
                                <Link href="/terms" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    Terms of Service
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
                <div className="mt-8 border-t pt-8">
                    <p className="text-center text-sm text-muted-foreground">
                        &copy; {new Date().getFullYear()} StayBooker. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    );
}
