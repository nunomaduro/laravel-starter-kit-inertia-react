import { Head, Link, router } from '@inertiajs/react';
import { BookingCard } from '@/components/booking/booking-card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { Booking, BookingStatus, BreadcrumbItem, PaginatedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'My Bookings', href: '/bookings' }];

type StatusTab = { label: string; value: BookingStatus | 'all' };

const STATUS_TABS: StatusTab[] = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Declined', value: 'declined' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

type Props = {
    bookings: PaginatedData<Booking>;
    status?: BookingStatus;
};

export default function BookingsIndex({ bookings, status }: Props) {
    const currentStatus = status ?? 'all';

    const handleCancel = (bookingId: string) => {
        if (confirm('Are you sure you want to cancel this booking?')) {
            router.patch(`/bookings/${bookingId}/cancel`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Bookings" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">My Bookings</h1>

                <div className="flex flex-wrap gap-2">
                    {STATUS_TABS.map((tab) => (
                        <Link
                            key={tab.value}
                            href={tab.value === 'all' ? '/bookings' : `/bookings?status=${tab.value}`}
                            preserveState
                        >
                            <Button variant={currentStatus === tab.value ? 'default' : 'ghost'} size="sm">
                                {tab.label}
                            </Button>
                        </Link>
                    ))}
                </div>

                {bookings.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16">
                        <p className="text-muted-foreground">You haven't made any bookings yet.</p>
                        <Link href="/search">
                            <Button variant="outline" className="mt-4">
                                Browse Properties
                            </Button>
                        </Link>
                    </div>
                ) : (
                    <div className="flex flex-col gap-4">
                        {bookings.data.map((booking) => (
                            <BookingCard
                                key={booking.id}
                                booking={booking}
                                actions={
                                    <>
                                        <Link href={`/bookings/${booking.id}`}>
                                            <Button variant="outline" size="sm">
                                                View Details
                                            </Button>
                                        </Link>
                                        {booking.status === 'pending' && (
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() => handleCancel(booking.id)}
                                            >
                                                Cancel
                                            </Button>
                                        )}
                                        {booking.status === 'completed' && (
                                            <Link href={`/bookings/${booking.id}#review`}>
                                                <Button variant="outline" size="sm">
                                                    Leave Review
                                                </Button>
                                            </Link>
                                        )}
                                    </>
                                }
                            />
                        ))}
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
        </AppLayout>
    );
}
