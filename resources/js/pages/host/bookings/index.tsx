import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { BookingCard } from '@/components/booking/booking-card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import HostLayout from '@/layouts/host-layout';
import type { Booking, BookingStatus, BreadcrumbItem, PaginatedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Bookings', href: '/host/bookings' }];

type StatusTab = { label: string; value: BookingStatus | 'all' };

const STATUS_TABS: StatusTab[] = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Declined', value: 'declined' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

type BookingWithGuest = Booking & { guest: { id: string; name: string; email: string } };

type Props = {
    bookings: PaginatedData<BookingWithGuest>;
    status?: BookingStatus;
};

export default function HostBookingsIndex({ bookings, status }: Props) {
    const currentStatus = status ?? 'all';
    const [dialogOpen, setDialogOpen] = useState(false);
    const [dialogAction, setDialogAction] = useState<'decline' | 'cancel'>('decline');
    const [selectedBookingId, setSelectedBookingId] = useState<string>('');
    const [reason, setReason] = useState('');

    const handleApprove = (bookingId: string) => {
        router.patch(`/host/bookings/${bookingId}`, { status: 'approved' });
    };

    const openDeclineDialog = (bookingId: string) => {
        setSelectedBookingId(bookingId);
        setDialogAction('decline');
        setReason('');
        setDialogOpen(true);
    };

    const openCancelDialog = (bookingId: string) => {
        setSelectedBookingId(bookingId);
        setDialogAction('cancel');
        setReason('');
        setDialogOpen(true);
    };

    const handleDialogSubmit = () => {
        const data: Record<string, string> = dialogAction === 'decline'
            ? { status: 'declined', reason }
            : { status: 'cancelled', reason };
        router.patch(`/host/bookings/${selectedBookingId}`, data);
        setDialogOpen(false);
    };

    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title="Booking Requests" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Booking Requests</h1>

                <div className="flex flex-wrap gap-2">
                    {STATUS_TABS.map((tab) => (
                        <Link
                            key={tab.value}
                            href={tab.value === 'all' ? '/host/bookings' : `/host/bookings?status=${tab.value}`}
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
                        <p className="text-muted-foreground">No bookings found.</p>
                    </div>
                ) : (
                    <div className="flex flex-col gap-4">
                        {bookings.data.map((booking) => (
                            <BookingCard
                                key={booking.id}
                                booking={booking}
                                actions={
                                    <>
                                        <span className="mr-auto text-sm text-muted-foreground">
                                            Guest: {booking.guest.name}
                                        </span>
                                        {booking.status === 'pending' && (
                                            <>
                                                <Button size="sm" onClick={() => handleApprove(booking.id)}>
                                                    Approve
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() => openDeclineDialog(booking.id)}
                                                >
                                                    Decline
                                                </Button>
                                            </>
                                        )}
                                        {booking.status === 'approved' && (
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() => openCancelDialog(booking.id)}
                                            >
                                                Cancel
                                            </Button>
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

            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{dialogAction === 'decline' ? 'Decline Booking' : 'Cancel Booking'}</DialogTitle>
                        <DialogDescription>
                            {dialogAction === 'decline'
                                ? 'Optionally provide a reason for declining this booking.'
                                : 'Please provide a reason for cancelling this booking.'}
                        </DialogDescription>
                    </DialogHeader>
                    <div>
                        <Label htmlFor="reason">Reason</Label>
                        <Input
                            id="reason"
                            value={reason}
                            onChange={(e) => setReason(e.target.value)}
                            placeholder="Enter reason..."
                            className="mt-1"
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDialogOpen(false)}>
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleDialogSubmit}
                            disabled={dialogAction === 'cancel' && !reason.trim()}
                        >
                            {dialogAction === 'decline' ? 'Decline' : 'Cancel Booking'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </HostLayout>
    );
}
