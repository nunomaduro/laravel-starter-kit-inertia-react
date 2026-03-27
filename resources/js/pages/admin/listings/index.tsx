import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { StatusBadge } from '@/components/booking/status-badge';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin-layout';
import type { BreadcrumbItem, PaginatedData, PropertySummary } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Listings', href: '/admin/listings' }];

type ListingWithHost = PropertySummary & { host: { id: string; name: string } };

type Props = {
    listings: PaginatedData<ListingWithHost>;
};

export default function AdminListingsIndex({ listings }: Props) {
    const [rejectDialogOpen, setRejectDialogOpen] = useState(false);
    const [selectedPropertyId, setSelectedPropertyId] = useState('');
    const [rejectReason, setRejectReason] = useState('');

    const handleApprove = (propertyId: string) => {
        router.patch(`/admin/listings/${propertyId}`, { status: 'approved' });
    };

    const openRejectDialog = (propertyId: string) => {
        setSelectedPropertyId(propertyId);
        setRejectReason('');
        setRejectDialogOpen(true);
    };

    const handleReject = () => {
        router.patch(`/admin/listings/${selectedPropertyId}`, { status: 'rejected', reason: rejectReason });
        setRejectDialogOpen(false);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Listing Approval" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Listing Approval Queue</h1>

                {listings.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16">
                        <p className="text-muted-foreground">No pending listings to review.</p>
                    </div>
                ) : (
                    <div className="flex flex-col gap-4">
                        {listings.data.map((listing) => (
                            <Card key={listing.id}>
                                <CardContent className="pt-0">
                                    <div className="flex gap-4">
                                        <div className="size-24 shrink-0 overflow-hidden rounded-lg">
                                            {listing.cover_image ? (
                                                <img
                                                    src={listing.cover_image}
                                                    alt={listing.name}
                                                    className="size-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex size-full items-center justify-center bg-neutral-100 dark:bg-neutral-800">
                                                    <span className="text-xs text-muted-foreground">No img</span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex items-start justify-between gap-2">
                                                <div>
                                                    <h3 className="font-semibold">{listing.name}</h3>
                                                    <div className="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                                        <Badge variant="outline" className="capitalize">
                                                            {listing.type}
                                                        </Badge>
                                                        <span>
                                                            {listing.city}, {listing.country}
                                                        </span>
                                                    </div>
                                                </div>
                                                <StatusBadge status={listing.status} />
                                            </div>
                                            <p className="mt-2 text-sm text-muted-foreground">
                                                Host: {listing.host.name}
                                            </p>
                                            <div className="mt-4 flex gap-2">
                                                <Link href={`/properties/${listing.slug}`}>
                                                    <Button variant="outline" size="sm">
                                                        View Details
                                                    </Button>
                                                </Link>
                                                {listing.status === 'pending' && (
                                                    <>
                                                        <Button size="sm" onClick={() => handleApprove(listing.id)}>
                                                            Approve
                                                        </Button>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => openRejectDialog(listing.id)}
                                                        >
                                                            Reject
                                                        </Button>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                {(listings.prev_page_url || listings.next_page_url) && (
                    <div className="flex items-center justify-between">
                        {listings.prev_page_url ? (
                            <Link href={listings.prev_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Previous
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                        <span className="text-sm text-muted-foreground">
                            Page {listings.current_page} of {listings.last_page}
                        </span>
                        {listings.next_page_url ? (
                            <Link href={listings.next_page_url} preserveState>
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

            <Dialog open={rejectDialogOpen} onOpenChange={setRejectDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Reject Listing</DialogTitle>
                        <DialogDescription>Provide a reason for rejecting this listing.</DialogDescription>
                    </DialogHeader>
                    <div>
                        <Label htmlFor="reject-reason">Reason</Label>
                        <Input
                            id="reject-reason"
                            value={rejectReason}
                            onChange={(e) => setRejectReason(e.target.value)}
                            placeholder="Enter rejection reason..."
                            className="mt-1"
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setRejectDialogOpen(false)}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleReject} disabled={!rejectReason.trim()}>
                            Reject
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
