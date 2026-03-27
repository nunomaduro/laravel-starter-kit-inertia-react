import { Head, Link } from '@inertiajs/react';
import {
    BookOpen,
    Building,
    CalendarCheck,
    Clock,
    DollarSign,
    Heart,
    MessageSquare,
    Search,
} from 'lucide-react';
import { BookingCard } from '@/components/booking/booking-card';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Booking, BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

type Props = {
    role: string;
    stats: {
        total_bookings: number;
        pending_bookings: number;
        completed_bookings: number;
        wishlisted_properties: number;
    };
    recentBookings: Booking[];
};

const guestStatCards = [
    {
        key: 'total_bookings' as const,
        label: 'My Bookings',
        icon: CalendarCheck,
    },
    {
        key: 'pending_bookings' as const,
        label: 'Pending',
        icon: Clock,
    },
    {
        key: 'completed_bookings' as const,
        label: 'Completed',
        icon: DollarSign,
    },
    {
        key: 'wishlisted_properties' as const,
        label: 'Wishlisted',
        icon: Heart,
    },
];

export default function Dashboard({ role, stats, recentBookings }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">
                    Welcome back!
                </h1>

                {role === 'host' && (
                    <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950">
                        <p className="text-sm text-blue-800 dark:text-blue-200">
                            You are also a host.{' '}
                            <Link
                                href="/host/dashboard"
                                className="font-semibold underline"
                            >
                                Go to Host Dashboard
                            </Link>{' '}
                            to manage your properties and bookings.
                        </p>
                    </div>
                )}

                {role === 'admin' && (
                    <div className="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-800 dark:bg-purple-950">
                        <p className="text-sm text-purple-800 dark:text-purple-200">
                            You are an admin.{' '}
                            <Link
                                href="/admin/users"
                                className="font-semibold underline"
                            >
                                Go to Admin Panel
                            </Link>{' '}
                            to manage users, listings, and reviews.
                        </p>
                    </div>
                )}

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {guestStatCards.map((card) => {
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
                                        {stats[card.key]}
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">
                        Recent Bookings
                    </h2>
                    <div className="flex gap-2">
                        <Link href="/search">
                            <Button size="sm" variant="outline">
                                <Search className="mr-1 size-4" />
                                Search Properties
                            </Button>
                        </Link>
                        <Link href="/bookings">
                            <Button size="sm" variant="outline">
                                View All Bookings
                            </Button>
                        </Link>
                    </div>
                </div>

                {recentBookings.length === 0 ? (
                    <div className="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed py-12">
                        <BookOpen className="size-12 text-muted-foreground" />
                        <div className="text-center">
                            <p className="font-medium">
                                No bookings yet
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Start exploring properties and make
                                your first booking!
                            </p>
                        </div>
                        <Link href="/search">
                            <Button>
                                <Search className="mr-1 size-4" />
                                Browse Properties
                            </Button>
                        </Link>
                    </div>
                ) : (
                    <div className="flex flex-col gap-4">
                        {recentBookings.map((booking) => (
                            <BookingCard
                                key={booking.id}
                                booking={booking}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
