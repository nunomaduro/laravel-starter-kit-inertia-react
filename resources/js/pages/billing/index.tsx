import CreditBalanceCard from '@/components/billing/credit-balance-card';
import CurrentPlanCard from '@/components/billing/current-plan-card';
import RecentInvoicesCard from '@/components/billing/recent-invoices-card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Billing', href: '/billing' }];

interface Props {
    organization: { id: number; name: string; billing_email?: string };
    creditBalance: number;
    activePlan: { id: number; name: string } | null;
    isOnTrial: boolean;
    invoices: {
        id: number;
        number: string;
        status: string;
        total: number;
        currency: string;
    }[];
}

export default function BillingIndex() {
    const { creditBalance, activePlan, isOnTrial, invoices } = usePage<
        Props & SharedData
    >().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h2 className="font-mono text-lg font-medium tracking-tight">Billing</h2>

                <div className="grid gap-4 md:grid-cols-2">
                    <CurrentPlanCard
                        activePlan={activePlan}
                        isOnTrial={isOnTrial}
                    />
                    <CreditBalanceCard creditBalance={creditBalance} />
                </div>

                <RecentInvoicesCard invoices={invoices} />
            </div>
        </AppLayout>
    );
}
