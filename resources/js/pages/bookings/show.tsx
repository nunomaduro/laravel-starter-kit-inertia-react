import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { PriceBreakdown } from '@/components/booking/price-breakdown';
import { StarRating } from '@/components/booking/star-rating';
import { StatusBadge } from '@/components/booking/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import type { Booking, BreadcrumbItem, Property, Review } from '@/types';

type Props = {
    booking: Booking & { property: Property; review?: Review };
};

export default function BookingShow({ booking }: Props) {
    const [rating, setRating] = useState(0);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'My Bookings', href: '/bookings' },
        { title: booking.property.name, href: `/bookings/${booking.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Booking - ${booking.property.name}`} />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">{booking.property.name}</h1>
                    <StatusBadge status={booking.status} />
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Booking Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Room Type</span>
                                <span>{booking.room_type.name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Check-in</span>
                                <span>{booking.check_in}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Check-out</span>
                                <span>{booking.check_out}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Guests</span>
                                <span>{booking.guests_count}</span>
                            </div>
                            {booking.notes && (
                                <>
                                    <Separator />
                                    <div>
                                        <span className="text-muted-foreground">Notes</span>
                                        <p className="mt-1">{booking.notes}</p>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Price Breakdown</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <PriceBreakdown priceBreakdown={booking.price_breakdown} totalPrice={booking.total_price} />
                        </CardContent>
                    </Card>
                </div>

                {booking.status === 'cancelled' && booking.cancellation_reason && (
                    <Card className="border-destructive">
                        <CardHeader>
                            <CardTitle className="text-destructive">Cancellation</CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm">
                            <p className="text-muted-foreground">
                                Cancelled by: <span className="capitalize">{booking.cancelled_by}</span>
                            </p>
                            <p className="mt-2">{booking.cancellation_reason}</p>
                        </CardContent>
                    </Card>
                )}

                {booking.status === 'completed' && !booking.review && (
                    <Card id="review">
                        <CardHeader>
                            <CardTitle>Leave a Review</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form action={`/bookings/${booking.id}/review`} method="post" className="flex flex-col gap-4">
                                {({ processing, errors }) => (
                                    <>
                                        <div>
                                            <Label>Rating</Label>
                                            <div className="mt-2">
                                                <StarRating rating={rating} size="lg" interactive onChange={setRating} />
                                            </div>
                                            <input type="hidden" name="rating" value={rating} />
                                            {errors.rating && (
                                                <p className="mt-1 text-sm text-destructive">{errors.rating}</p>
                                            )}
                                        </div>
                                        <div>
                                            <Label htmlFor="review-comment">Comment</Label>
                                            <textarea
                                                id="review-comment"
                                                name="comment"
                                                rows={4}
                                                className="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                                placeholder="Share your experience..."
                                            />
                                            {errors.comment && (
                                                <p className="mt-1 text-sm text-destructive">{errors.comment}</p>
                                            )}
                                        </div>
                                        <div className="flex justify-end">
                                            <Button type="submit" disabled={processing || rating === 0}>
                                                {processing && <Spinner />}
                                                Submit Review
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                )}

                {booking.review && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Your Review</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <StarRating rating={booking.review.rating} />
                            <p className="text-sm">{booking.review.comment}</p>
                        </CardContent>
                    </Card>
                )}

                <div className="flex justify-start">
                    <Link href="/bookings">
                        <Button variant="outline">Back to Bookings</Button>
                    </Link>
                </div>
            </div>
        </AppLayout>
    );
}
