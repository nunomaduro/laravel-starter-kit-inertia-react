import { Link } from '@inertiajs/react';
import { MapPin } from 'lucide-react';
import { StarRating } from '@/components/booking/star-rating';
import { Card, CardContent } from '@/components/ui/card';
import type { PropertySummary } from '@/types';

type PropertyCardProps = {
    property: PropertySummary;
};

export function PropertyCard({ property }: PropertyCardProps) {
    return (
        <Link href={'/properties/' + property.slug}>
            <Card className="group gap-0 overflow-hidden py-0 transition-shadow hover:shadow-lg">
                <div className="relative aspect-[4/3] overflow-hidden">
                    {property.cover_image ? (
                        <img
                            src={property.cover_image}
                            alt={property.name}
                            className="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                    ) : (
                        <div className="flex size-full items-center justify-center bg-neutral-100 dark:bg-neutral-800">
                            <span className="text-sm text-muted-foreground">No image</span>
                        </div>
                    )}
                    {property.is_featured && (
                        <span className="absolute top-3 left-3 rounded-full bg-amber-500 px-2.5 py-0.5 text-xs font-semibold text-white">
                            Featured
                        </span>
                    )}
                </div>
                <CardContent className="p-4">
                    <div className="flex items-start justify-between gap-2">
                        <h3 className="truncate font-semibold">{property.name}</h3>
                        <span className="shrink-0 text-xs capitalize text-muted-foreground">{property.type}</span>
                    </div>
                    <div className="mt-1 flex items-center gap-1 text-sm text-muted-foreground">
                        <MapPin className="size-3.5" />
                        <span className="truncate">
                            {property.city}, {property.country}
                        </span>
                    </div>
                    <div className="mt-3 flex items-center justify-between">
                        <div className="flex items-center gap-1.5">
                            <StarRating rating={property.average_rating} size="sm" />
                            <span className="text-xs text-muted-foreground">({property.reviews_count})</span>
                        </div>
                        <div className="text-right">
                            <span className="text-sm font-semibold">
                                From {property.min_price?.toLocaleString() ?? '—'} LYD
                            </span>
                            <span className="text-xs text-muted-foreground">/night</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </Link>
    );
}
