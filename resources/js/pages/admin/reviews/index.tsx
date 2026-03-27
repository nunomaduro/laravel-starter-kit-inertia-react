import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { ReviewCard } from '@/components/booking/review-card';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AdminLayout from '@/layouts/admin-layout';
import type { BreadcrumbItem, PaginatedData, PropertySummary, Review } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Reviews', href: '/admin/reviews' }];

type ReviewWithProperty = Review & { property: PropertySummary };

type Props = {
    reviews: PaginatedData<ReviewWithProperty>;
};

export default function AdminReviewsIndex({ reviews }: Props) {
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [selectedReviewId, setSelectedReviewId] = useState('');

    const openDeleteDialog = (reviewId: string) => {
        setSelectedReviewId(reviewId);
        setDeleteDialogOpen(true);
    };

    const handleDelete = () => {
        router.delete(`/admin/reviews/${selectedReviewId}`);
        setDeleteDialogOpen(false);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Review Moderation" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Review Moderation</h1>

                {reviews.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16">
                        <p className="text-muted-foreground">No reviews to moderate.</p>
                    </div>
                ) : (
                    <div className="flex flex-col gap-4">
                        {reviews.data.map((review) => (
                            <Card key={review.id}>
                                <CardContent className="pt-0">
                                    <div className="mb-3 flex items-center justify-between">
                                        <Link
                                            href={`/properties/${review.property.slug}`}
                                            className="text-sm font-medium text-primary hover:underline"
                                        >
                                            {review.property.name}
                                        </Link>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            onClick={() => openDeleteDialog(review.id)}
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                    <ReviewCard review={review} />
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                {(reviews.prev_page_url || reviews.next_page_url) && (
                    <div className="flex items-center justify-between">
                        {reviews.prev_page_url ? (
                            <Link href={reviews.prev_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Previous
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                        <span className="text-sm text-muted-foreground">
                            Page {reviews.current_page} of {reviews.last_page}
                        </span>
                        {reviews.next_page_url ? (
                            <Link href={reviews.next_page_url} preserveState>
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

            <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Review</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this review? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteDialogOpen(false)}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleDelete}>
                            Delete Review
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
