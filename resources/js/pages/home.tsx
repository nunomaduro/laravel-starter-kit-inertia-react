import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { PropertyCard } from '@/components/booking/property-card';
import { SearchBar } from '@/components/booking/search-bar';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import type { PropertySummary } from '@/types';

type HomeProps = {
    featuredProperties: PropertySummary[];
};

export default function Home({ featuredProperties }: HomeProps) {
    return (
        <PublicLayout>
            <Head title="Find Your Perfect Stay" />

            <section className="relative overflow-hidden bg-gradient-to-br from-primary/10 via-primary/5 to-transparent py-20 dark:from-primary/20 dark:via-primary/10 lg:py-32">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-3xl text-center">
                        <h1 className="text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                            Find Your Perfect Stay
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground sm:text-xl">
                            Discover resorts, hotels, and villas for your next unforgettable getaway.
                        </p>
                    </div>
                    <div className="mt-10">
                        <SearchBar variant="hero" />
                    </div>
                </div>
            </section>

            {featuredProperties.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between">
                        <h2 className="text-2xl font-bold tracking-tight">Featured Properties</h2>
                        <Button variant="ghost" asChild>
                            <Link href="/search" className="gap-1">
                                View all
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                    <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {featuredProperties.map((property) => (
                            <PropertyCard key={property.id} property={property} />
                        ))}
                    </div>
                </section>
            )}

            <section className="border-t bg-neutral-50 dark:bg-neutral-950">
                <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">Become a Host</h2>
                        <p className="mt-4 text-muted-foreground">
                            List your property and start earning. Join thousands of hosts who trust our platform.
                        </p>
                        <div className="mt-8">
                            <Button size="lg" asChild>
                                <Link href="/host/register">
                                    Start Hosting
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
