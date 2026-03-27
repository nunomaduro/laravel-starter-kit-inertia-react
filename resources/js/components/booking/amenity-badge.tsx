import {
    AirVent,
    Car,
    CookingPot,
    Dumbbell,
    Flame,
    type LucideIcon,
    ShowerHead,
    Sparkles,
    Tv,
    Waves,
    Wifi,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';

type AmenityBadgeProps = {
    amenity: string;
};

const amenityIcons: Record<string, LucideIcon> = {
    wifi: Wifi,
    pool: Waves,
    parking: Car,
    gym: Dumbbell,
    spa: Sparkles,
    tv: Tv,
    'air conditioning': AirVent,
    kitchen: CookingPot,
    'hot tub': Flame,
    shower: ShowerHead,
};

export function AmenityBadge({ amenity }: AmenityBadgeProps) {
    const Icon = amenityIcons[amenity.toLowerCase()];

    return (
        <Badge variant="outline" className="gap-1.5 py-1">
            {Icon && <Icon className="size-3.5" />}
            <span className="capitalize">{amenity}</span>
        </Badge>
    );
}
