import { Link } from '@inertiajs/react';
import { FileText, Receipt } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { EmptyState } from '@/components/ui/empty-state';

interface Invoice {
    id: number;
    number: string;
    status: string;
    total: number;
    currency: string;
}

interface Props {
    invoices: Invoice[];
    viewAllHref?: string;
}

export default function RecentInvoicesCard({
    invoices,
    viewAllHref = '/billing/invoices',
}: Props) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <FileText className="size-5" />
                    Recent invoices
                </CardTitle>
                <CardDescription>View and download invoices</CardDescription>
            </CardHeader>
            <CardContent>
                {invoices.length === 0 ? (
                    <EmptyState
                        icon={<Receipt className="size-5" />}
                        title="No invoices yet"
                        description="Your invoices will appear here after your first billing cycle."
                    />
                ) : (
                    <ul className="space-y-2">
                        {invoices.map((inv) => (
                            <li
                                key={inv.id}
                                className="flex items-center justify-between text-sm"
                            >
                                <span>
                                    {inv.number} — {inv.status}
                                </span>
                                <span>
                                    {inv.currency.toUpperCase()}{' '}
                                    {(inv.total / 100).toFixed(2)}
                                </span>
                            </li>
                        ))}
                    </ul>
                )}
                <Button variant="link" className="mt-2 p-0" asChild>
                    <Link href={viewAllHref}>View all invoices</Link>
                </Button>
            </CardContent>
        </Card>
    );
}
