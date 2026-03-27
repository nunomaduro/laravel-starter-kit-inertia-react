import { CalendarDays, Users } from 'lucide-react';
import type { ReactNode } from 'react';
import { StatusBadge } from '@/components/booking/status-badge';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import type { Booking } from '@/types';

type BookingCardProps = {
    booking: Booking;
    actions?: ReactNode;
};

export function BookingCard({ booking, actions }: BookingCardProps) {
    return (
        <Card>
            <CardContent className="pt-0">
                <div className="flex gap-4">
                    <div className="size-20 shrink-0 overflow-hidden rounded-lg">
                        {booking.property.cover_image ? (
                            <img
                                src={booking.property.cover_image}
                                alt={booking.property.name}
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
                            <h4 className="font-semibold">{booking.property.name}</h4>
                            <StatusBadge status={booking.status} />
                        </div>
                        <p className="mt-0.5 text-sm text-muted-foreground">{booking.room_type.name}</p>
                        <div className="mt-2 flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                            <span className="flex items-center gap-1">
                                <CalendarDays className="size-4" />
                                {booking.check_in} &rarr; {booking.check_out}
                            </span>
                            <span className="flex items-center gap-1">
                                <Users className="size-4" />
                                {booking.guests_count} guests
                            </span>
                        </div>
                        <div className="mt-2 text-sm font-semibold">
                            Total: {booking.total_price.toLocaleString()} LYD
                        </div>
                    </div>
                </div>
            </CardContent>
            {actions && <CardFooter className="justify-end gap-2">{actions}</CardFooter>}
        </Card>
    );
}
