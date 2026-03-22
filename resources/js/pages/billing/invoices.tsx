import { EmptyState } from '@/components/ui/empty-state';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { FileText, Receipt } from 'lucide-react';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Billing', href: '/billing' },
    { title: 'Invoices', href: '/billing/invoices' },
];

interface Invoice {
    id: number;
    number: string;
    status: string;
    total: number;
    currency: string;
    created_at: string;
}

interface PaginatedInvoices {
    data: Invoice[];
    current_page: number;
    last_page: number;
    per_page: number;
}

interface Props {
    invoices: PaginatedInvoices;
}

export default function BillingInvoices() {
    const { invoices } = usePage<Props & SharedData>().props;
    const { data, current_page, last_page } = invoices;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Invoices" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h2 className="font-mono text-lg font-medium tracking-tight">Invoices</h2>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileText className="size-5" />
                            Invoice history
                        </CardTitle>
                        <CardDescription>
                            View and download your invoices
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {data.length === 0 ? (
                            <EmptyState
                                icon={<Receipt className="size-5" />}
                                title="No invoices yet"
                                description="Your invoices will appear here after your first billing cycle."
                            />
                        ) : (
                            <ul className="space-y-2">
                                {data.map((inv) => (
                                    <li
                                        key={inv.id}
                                        className="flex items-center justify-between text-sm"
                                    >
                                        <span>
                                            {inv.number} — {inv.status}
                                        </span>
                                        <span>
                                            {inv.currency.toUpperCase()}{' '}
                                            {(inv.total / 100).toFixed(2)} —{' '}
                                            {new Date(
                                                inv.created_at,
                                            ).toLocaleDateString()}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                        {last_page > 1 && (
                            <p className="mt-2 text-sm text-muted-foreground">
                                Page {current_page} of {last_page}
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
