import { Moon, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import type { RoomType } from '@/types';

type RoomTypeCardProps = {
    roomType: RoomType;
    onSelect?: (id: string) => void;
};

export function RoomTypeCard({ roomType, onSelect }: RoomTypeCardProps) {
    return (
        <Card>
            <CardContent className="pt-0">
                <div className="flex items-start justify-between gap-4">
                    <div className="flex-1">
                        <h4 className="font-semibold">{roomType.name}</h4>
                        {roomType.description && (
                            <p className="mt-1 text-sm text-muted-foreground">{roomType.description}</p>
                        )}
                        <div className="mt-3 flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                            <span className="flex items-center gap-1">
                                <Users className="size-4" />
                                Up to {roomType.max_guests} guests
                            </span>
                            <span className="flex items-center gap-1">
                                <Moon className="size-4" />
                                {roomType.min_nights} night min
                                {roomType.max_nights && `, ${roomType.max_nights} max`}
                            </span>
                        </div>
                    </div>
                    <div className="text-right">
                        <div className="text-lg font-bold">{roomType.base_price_per_night.toLocaleString()} LYD</div>
                        <div className="text-xs text-muted-foreground">per night</div>
                    </div>
                </div>
            </CardContent>
            {onSelect && (
                <CardFooter>
                    <Button onClick={() => onSelect(roomType.id)} className="ml-auto">
                        Select Room
                    </Button>
                </CardFooter>
            )}
        </Card>
    );
}
