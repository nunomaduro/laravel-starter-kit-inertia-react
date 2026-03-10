import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

interface Plan {
    id: number;
    name: string;
    description: string;
    price: number;
    currency: string;
    interval: string;
}

interface Props {
    plans: Plan[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pricing', href: '/pricing' }];

export default function Pricing() {
    const { plans, auth } = usePage<Props & SharedData>().props;
    const isAuthenticated = !!auth?.user;

    const content = (
        <div className="min-h-screen bg-muted/30 py-12">
            <div className="container mx-auto px-4">
                <div className="mb-12 text-center">
                    <h1 className="text-3xl font-bold">Pricing</h1>
                    <p className="mt-2 text-muted-foreground">
                        Choose the plan that fits your needs
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {(plans ?? []).map((plan) => (
                        <Card key={plan.id} className="flex flex-col">
                            <CardHeader>
                                <CardTitle>{plan.name}</CardTitle>
                                <CardDescription>
                                    {plan.description}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-1 flex-col">
                                <p className="mb-4 text-2xl font-bold">
                                    {plan.currency.toUpperCase()}{' '}
                                    {plan.price.toFixed(2)}
                                    <span className="text-sm font-normal text-muted-foreground">
                                        /{plan.interval}
                                    </span>
                                </p>
                                <Button className="mt-auto" asChild>
                                    <Link href="/register">Get started</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {(plans ?? []).length === 0 && (
                    <div className="text-center text-muted-foreground">
                        No plans available at the moment.
                    </div>
                )}

                <div className="mt-12 flex justify-center">
                    <p className="text-sm text-muted-foreground">
                        Need a custom enterprise plan?{' '}
                        <Link
                            href="/enterprise"
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Contact us
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    );

    if (isAuthenticated) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Pricing" />
                {content}
            </AppLayout>
        );
    }

    return (
        <>
            <Head title="Pricing" />
            {content}
        </>
    );
}
