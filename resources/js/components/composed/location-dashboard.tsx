import * as React from 'react';
import { MapPinIcon, NavigationIcon, StarIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { MarkersMap, type MapMarkerData } from '@/components/maps/markers-map';

export interface LocationItem {
    id: string;
    name: string;
    address?: string;
    distance?: string;
    category?: string;
    rating?: number;
    longitude: number;
    latitude: number;
    url?: string;
}

export interface LocationDashboardProps {
    locations: LocationItem[];
    center?: [number, number];
    zoom?: number;
    title?: string;
    searchable?: boolean;
    onSelectLocation?: (location: LocationItem) => void;
    selectedId?: string;
    className?: string;
    mapHeight?: number;
}

function StarRating({ rating }: { rating: number }) {
    return (
        <div className="flex items-center gap-0.5" aria-label={`${rating} out of 5 stars`}>
            {Array.from({ length: 5 }).map((_, i) => (
                <StarIcon
                    key={i}
                    className={cn(
                        'size-3',
                        i < Math.round(rating)
                            ? 'fill-amber-400 text-amber-400'
                            : 'text-muted-foreground/30',
                    )}
                />
            ))}
            <span className="ml-1 text-[10px] text-muted-foreground">{rating.toFixed(1)}</span>
        </div>
    );
}

function LocationDashboard({
    locations,
    center,
    zoom = 12,
    title = 'Locations',
    searchable = true,
    onSelectLocation,
    selectedId,
    className,
    mapHeight = 300,
}: LocationDashboardProps) {
    const [search, setSearch] = React.useState('');
    const [localSelected, setLocalSelected] = React.useState<string | null>(selectedId ?? null);

    const activeId = selectedId ?? localSelected;

    const filtered = locations.filter(
        (loc) =>
            !search ||
            loc.name.toLowerCase().includes(search.toLowerCase()) ||
            loc.category?.toLowerCase().includes(search.toLowerCase()) ||
            loc.address?.toLowerCase().includes(search.toLowerCase()),
    );

    const markers: MapMarkerData[] = locations.map((loc) => ({
        id: loc.id,
        longitude: loc.longitude,
        latitude: loc.latitude,
        label: loc.name,
        description: loc.address,
        color: loc.id === activeId ? '#3b82f6' : '#6b7280',
    }));

    const mapCenter = React.useMemo<[number, number] | undefined>(() => {
        if (center) return center;
        const active = locations.find((l) => l.id === activeId);
        if (active) return [active.longitude, active.latitude];
        if (locations.length > 0) return [locations[0].longitude, locations[0].latitude];
        return undefined;
    }, [center, activeId, locations]);

    const handleSelect = (loc: LocationItem) => {
        setLocalSelected(loc.id);
        onSelectLocation?.(loc);
    };

    return (
        <div data-slot="location-dashboard" className={cn('grid gap-4 lg:grid-cols-[1fr_320px]', className)}>
            <Card className="overflow-hidden">
                <div style={{ height: `${mapHeight}px` }} className="relative w-full">
                    <MarkersMap
                        markers={markers}
                        center={mapCenter}
                        zoom={zoom}
                        className="!h-full rounded-none border-0"
                    />
                </div>
            </Card>

            <Card className="flex flex-col">
                <CardHeader className="pb-2">
                    <CardTitle className="text-sm">{title}</CardTitle>
                    {searchable && (
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search locations..."
                            className="h-8 text-sm"
                        />
                    )}
                </CardHeader>
                <CardContent className="flex-1 overflow-y-auto p-2">
                    {filtered.length === 0 ? (
                        <div className="flex h-32 items-center justify-center text-sm text-muted-foreground">
                            No locations found.
                        </div>
                    ) : (
                        <div className="space-y-1">
                            {filtered.map((loc) => (
                                <button
                                    key={loc.id}
                                    type="button"
                                    onClick={() => handleSelect(loc)}
                                    className={cn(
                                        'w-full rounded-lg border p-3 text-left transition-colors hover:bg-muted/50',
                                        activeId === loc.id &&
                                            'border-primary bg-primary/5',
                                    )}
                                >
                                    <div className="flex items-start gap-2">
                                        <MapPinIcon
                                            className={cn(
                                                'mt-0.5 size-4 shrink-0',
                                                activeId === loc.id
                                                    ? 'text-primary'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center justify-between gap-1">
                                                <p className="truncate text-sm font-medium">
                                                    {loc.name}
                                                </p>
                                                {loc.distance && (
                                                    <span className="shrink-0 text-[10px] text-muted-foreground">
                                                        {loc.distance}
                                                    </span>
                                                )}
                                            </div>
                                            {loc.address && (
                                                <p className="truncate text-xs text-muted-foreground">
                                                    {loc.address}
                                                </p>
                                            )}
                                            <div className="mt-1 flex items-center gap-2">
                                                {loc.category && (
                                                    <Badge
                                                        variant="secondary"
                                                        className="px-1.5 py-0 text-[10px]"
                                                    >
                                                        {loc.category}
                                                    </Badge>
                                                )}
                                                {loc.rating !== undefined && (
                                                    <StarRating rating={loc.rating} />
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    {loc.url && (
                                        <a
                                            href={loc.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            onClick={(e) => e.stopPropagation()}
                                            className="mt-1.5 flex items-center gap-1 text-[10px] text-primary hover:underline"
                                        >
                                            <NavigationIcon className="size-3" />
                                            Directions
                                        </a>
                                    )}
                                </button>
                            ))}
                        </div>
                    )}
                </CardContent>
                {filtered.length > 0 && (
                    <div className="border-t px-4 py-2">
                        <p className="text-xs text-muted-foreground">
                            {filtered.length} location{filtered.length !== 1 ? 's' : ''}
                        </p>
                    </div>
                )}
            </Card>
        </div>
    );
}

export { LocationDashboard };
