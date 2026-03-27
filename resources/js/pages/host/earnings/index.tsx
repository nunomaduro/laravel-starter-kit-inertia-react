import { Head, Link } from '@inertiajs/react';
import { CheckCircle, Clock, DollarSign, TrendingUp } from 'lucide-react';
import { StatusBadge } from '@/components/booking/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import HostLayout from '@/layouts/host-layout';
import type { Booking, BreadcrumbItem, EarningsSummary, PaginatedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Earnings', href: '/host/earnings' }];

type Props = {
    summary: EarningsSummary;
    bookings: PaginatedData<Booking>;
};

const summaryCards = [
    { key: 'total_earnings', label: 'Total Earnings', icon: DollarSign },
    { key: 'this_month', label: 'This Month', icon: TrendingUp },
    { key: 'pending_payouts', label: 'Pending Payouts', icon: Clock },
    { key: 'completed_bookings', label: 'Completed Bookings', icon: CheckCircle },
] as const;

export default function HostEarningsIndex({ summary, bookings }: Props) {
    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title="Earnings" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Earnings</h1>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {summaryCards.map((card) => {
                        const Icon = card.icon;
                        const value = summary[card.key];
                        const isMonetary = card.key !== 'completed_bookings';
                        return (
                            <Card key={card.key}>
                                <CardHeader className="flex flex-row items-center justify-between pb-2">
                                    <CardTitle className="text-sm font-medium text-muted-foreground">
                                        {card.label}
                                    </CardTitle>
                                    <Icon className="size-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        {isMonetary ? `${value.toLocaleString()} LYD` : value}
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                <h2 className="text-lg font-semibold">Completed Bookings</h2>

                {bookings.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-12">
                        <p className="text-muted-foreground">No completed bookings yet.</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b bg-muted/50">
                                    <th className="px-4 py-3 text-left font-medium">Property</th>
                                    <th className="px-4 py-3 text-left font-medium">Room</th>
                                    <th className="px-4 py-3 text-left font-medium">Dates</th>
                                    <th className="px-4 py-3 text-left font-medium">Status</th>
                                    <th className="px-4 py-3 text-right font-medium">Total</th>
                                    <th className="px-4 py-3 text-right font-medium">Commission</th>
                                    <th className="px-4 py-3 text-right font-medium">Payout</th>
                                </tr>
                            </thead>
                            <tbody>
                                {bookings.data.map((booking) => (
                                    <tr key={booking.id} className="border-b last:border-b-0">
                                        <td className="px-4 py-3">{booking.property.name}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{booking.room_type.name}</td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {booking.check_in} - {booking.check_out}
                                        </td>
                                        <td className="px-4 py-3">
                                            <StatusBadge status={booking.status} />
                                        </td>
                                        <td className="px-4 py-3 text-right">{booking.total_price.toLocaleString()} LYD</td>
                                        <td className="px-4 py-3 text-right text-destructive">
                                            -{booking.commission_amount.toLocaleString()} LYD
                                        </td>
                                        <td className="px-4 py-3 text-right font-medium">
                                            {booking.host_payout.toLocaleString()} LYD
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {(bookings.prev_page_url || bookings.next_page_url) && (
                    <div className="flex items-center justify-between">
                        {bookings.prev_page_url ? (
                            <Link href={bookings.prev_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Previous
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                        <span className="text-sm text-muted-foreground">
                            Page {bookings.current_page} of {bookings.last_page}
                        </span>
                        {bookings.next_page_url ? (
                            <Link href={bookings.next_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Next
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                    </div>
                )}
            </div>
        </HostLayout>
    );
}
