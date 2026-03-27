import { Head, Link } from '@inertiajs/react';
import { Building, CalendarCheck, Clock, DollarSign } from 'lucide-react';
import { BookingCard } from '@/components/booking/booking-card';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import HostLayout from '@/layouts/host-layout';
import type { Booking, BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/host/dashboard' }];

type Props = {
    stats: {
        total_properties: number;
        total_bookings: number;
        pending_bookings: number;
        monthly_earnings: number;
    };
    recentBookings: Booking[];
};

const statCards = [
    { key: 'total_properties', label: 'Total Properties', icon: Building, format: (v: number) => v.toString() },
    { key: 'total_bookings', label: 'Total Bookings', icon: CalendarCheck, format: (v: number) => v.toString() },
    { key: 'pending_bookings', label: 'Pending Requests', icon: Clock, format: (v: number) => v.toString() },
    {
        key: 'monthly_earnings',
        label: "This Month's Earnings",
        icon: DollarSign,
        format: (v: number) => `${v.toLocaleString()} LYD`,
    },
] as const;

export default function HostDashboard({ stats, recentBookings }: Props) {
    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title="Host Dashboard" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Host Dashboard</h1>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {statCards.map((card) => {
                        const Icon = card.icon;
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
                                        {card.format(stats[card.key])}
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Recent Bookings</h2>
                    <div className="flex gap-2">
                        <Link href="/host/properties/create">
                            <Button size="sm">Add Property</Button>
                        </Link>
                        <Link href="/host/bookings">
                            <Button variant="outline" size="sm">
                                View All Bookings
                            </Button>
                        </Link>
                    </div>
                </div>

                {recentBookings.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-12">
                        <p className="text-muted-foreground">No recent bookings.</p>
                    </div>
                ) : (
                    <div className="flex flex-col gap-4">
                        {recentBookings.map((booking) => (
                            <BookingCard key={booking.id} booking={booking} />
                        ))}
                    </div>
                )}
            </div>
        </HostLayout>
    );
}
