import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Coins } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Billing', href: '/billing' },
    { title: 'Credits', href: '/billing/credits' },
];

interface CreditPack {
    id: number;
    name: string;
    credits: number;
    bonus_credits: number;
    price: number;
    currency: string;
}

interface CreditTransaction {
    id: number;
    amount: number;
    type: string;
    description: string | null;
    created_at: string;
}

interface Props {
    creditBalance: number;
    transactions: CreditTransaction[];
    creditPacks: CreditPack[];
    lemonSqueezyAvailable?: boolean;
}

export default function BillingCredits() {
    const { creditBalance, transactions, creditPacks, lemonSqueezyAvailable } =
        usePage<Props & SharedData>().props;

    function purchase(packId: number) {
        router.post('/billing/credits/purchase', {
            credit_pack_id: packId,
        });
    }

    function purchaseWithLemonSqueezy(packId: number) {
        router.post('/billing/credits/checkout/lemon-squeezy', {
            credit_pack_id: packId,
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Credits" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h2 className="font-mono text-lg font-medium tracking-tight">Credits</h2>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Coins className="size-5" />
                            Balance
                        </CardTitle>
                        <CardDescription>
                            You have {creditBalance} credits available.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Buy credits</CardTitle>
                        <CardDescription>
                            Purchase credit packs to use across the platform.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {creditPacks.map((pack) => (
                                <Card key={pack.id}>
                                    <CardHeader>
                                        <CardTitle>{pack.name}</CardTitle>
                                        <CardDescription>
                                            {pack.credits + pack.bonus_credits}{' '}
                                            credits
                                            {pack.bonus_credits > 0 &&
                                                ` (${pack.bonus_credits} bonus)`}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="mb-2 font-semibold">
                                            {pack.currency.toUpperCase()}{' '}
                                            {(pack.price / 100).toFixed(2)}
                                        </p>
                                        <div className="flex flex-wrap gap-2">
                                            <Button
                                                size="sm"
                                                onClick={() =>
                                                    purchase(pack.id)
                                                }
                                            >
                                                Add credits (manual)
                                            </Button>
                                            {lemonSqueezyAvailable && (
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        purchaseWithLemonSqueezy(
                                                            pack.id,
                                                        )
                                                    }
                                                >
                                                    Pay with Lemon Squeezy
                                                </Button>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                        {creditPacks.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                No credit packs available.
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Transaction history</CardTitle>
                        <CardDescription>
                            Recent credit activity
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {transactions.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No transactions yet.
                            </p>
                        ) : (
                            <ul className="space-y-2">
                                {transactions.map((tx) => (
                                    <li
                                        key={tx.id}
                                        className="flex items-center justify-between text-sm"
                                    >
                                        <span>
                                            {tx.description ?? tx.type} —{' '}
                                            {new Date(
                                                tx.created_at,
                                            ).toLocaleDateString()}
                                        </span>
                                        <span
                                            className={
                                                tx.amount >= 0
                                                    ? 'text-green-600'
                                                    : 'text-red-600'
                                            }
                                        >
                                            {tx.amount >= 0 ? '+' : ''}
                                            {tx.amount}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
