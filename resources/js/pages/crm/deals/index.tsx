import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'CRM', href: '/crm/contacts' },
    { title: 'Deals', href: '/crm/deals' },
];

interface Contact {
    id: number;
    first_name: string;
    last_name: string;
}

interface Deal {
    id: number;
    title: string;
    value: string;
    currency: string | null;
    stage: string;
    probability: number | null;
    expected_close_date: string | null;
    status: string;
    contact: Contact | null;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface Props {
    deals: PaginatedData<Deal>;
}

function stageBadgeClass(stage: string): string {
    switch (stage) {
        case 'qualified':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        case 'proposal':
            return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
        case 'negotiation':
            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'won':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
        case 'lost':
            return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
    }
}

export default function DealsIndex() {
    const { deals } = usePage<Props & SharedData>().props;
    const { flash } = usePage<{ flash?: { status?: string } } & SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Deals" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-lg font-medium">Deals</h2>
                    <Button asChild>
                        <Link href="/crm/deals/create">
                            <Plus className="mr-2 size-4" />
                            New deal
                        </Link>
                    </Button>
                </div>

                {flash?.status && (
                    <p className="text-sm text-emerald-600 dark:text-emerald-400">
                        {flash.status}
                    </p>
                )}

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Title</th>
                                <th className="px-4 py-3 text-left font-medium">Contact</th>
                                <th className="px-4 py-3 text-right font-medium">Value</th>
                                <th className="px-4 py-3 text-left font-medium">Stage</th>
                                <th className="px-4 py-3 text-right font-medium">Probability</th>
                                <th className="px-4 py-3 text-left font-medium">Expected Close</th>
                                <th className="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {deals.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                                        No deals found.
                                    </td>
                                </tr>
                            ) : (
                                deals.data.map((deal) => (
                                    <tr key={deal.id} className="transition-colors hover:bg-muted/50">
                                        <td className="px-4 py-3 font-medium">{deal.title}</td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {deal.contact
                                                ? `${deal.contact.first_name} ${deal.contact.last_name}`
                                                : '-'}
                                        </td>
                                        <td className="px-4 py-3 text-right tabular-nums">
                                            {deal.currency ?? '$'}{Number(deal.value).toLocaleString()}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize ${stageBadgeClass(deal.stage)}`}>
                                                {deal.stage}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right text-muted-foreground">
                                            {deal.probability != null ? `${deal.probability}%` : '-'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {deal.expected_close_date ?? '-'}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={`/crm/deals/${deal.id}/edit`}>
                                                    Edit
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {deals.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing page {deals.current_page} of {deals.last_page} ({deals.total} total)
                        </p>
                        <div className="flex gap-2">
                            {deals.prev_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={deals.prev_page_url}>Previous</Link>
                                </Button>
                            )}
                            {deals.next_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={deals.next_page_url}>Next</Link>
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
