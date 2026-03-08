import { Map, MapControls, MapMarker, useMap } from '@/components/ui/map';
import { cn } from '@/lib/utils';
import type MapLibreGL from 'maplibre-gl';
import { useCallback, useEffect, useState } from 'react';
import { OPEN_FREE_MAP_STYLES } from './base-map';

export type PickedLocation = {
    longitude: number;
    latitude: number;
};

/** Inner component that attaches click listener via useMap() */
function ClickListener({ onPick }: { onPick: (loc: PickedLocation) => void }) {
    const { map, isLoaded } = useMap();
    const onPickRef = { current: onPick };
    onPickRef.current = onPick;

    useEffect(() => {
        if (!isLoaded || !map) return;
        const handler = (e: MapLibreGL.MapMouseEvent) => {
            onPickRef.current({
                longitude: e.lngLat.lng,
                latitude: e.lngLat.lat,
            });
        };
        map.on('click', handler);
        return () => {
            map.off('click', handler);
        };
    }, [isLoaded, map]);

    return null;
}

type LocationPickerProps = {
    /** Controlled value */
    value?: PickedLocation | null;
    /** Callback when user picks a location */
    onChange?: (location: PickedLocation | null) => void;
    className?: string;
    center?: [number, number];
    zoom?: number;
    /** Placeholder text shown before selection */
    placeholder?: string;
};

export function LocationPicker({
    value,
    onChange,
    className,
    center = [-74.006, 40.7128],
    zoom = 10,
    placeholder = 'Click on the map to pick a location',
}: LocationPickerProps) {
    const [internal, setInternal] = useState<PickedLocation | null>(null);
    const picked = value !== undefined ? value : internal;

    const handlePick = useCallback(
        (loc: PickedLocation) => {
            setInternal(loc);
            onChange?.(loc);
        },
        [onChange],
    );

    const handleClear = useCallback(() => {
        setInternal(null);
        onChange?.(null);
    }, [onChange]);

    return (
        <div className={cn('flex flex-col gap-2', className)}>
            <div className="relative h-80 w-full overflow-hidden rounded-lg border">
                <Map
                    className="size-full cursor-crosshair"
                    styles={OPEN_FREE_MAP_STYLES}
                    center={center}
                    zoom={zoom}
                >
                    <MapControls />
                    <ClickListener onPick={handlePick} />
                    {picked && (
                        <MapMarker
                            longitude={picked.longitude}
                            latitude={picked.latitude}
                            color="#ef4444"
                        >
                            <span />
                        </MapMarker>
                    )}
                </Map>

                {!picked && (
                    <div className="pointer-events-none absolute inset-0 flex items-end justify-center pb-4">
                        <span className="rounded-full bg-background/90 px-3 py-1 text-xs text-muted-foreground shadow backdrop-blur">
                            {placeholder}
                        </span>
                    </div>
                )}
            </div>

            {picked && (
                <div className="flex items-center gap-3 rounded-md border bg-muted/40 px-3 py-2 text-sm">
                    <span className="text-muted-foreground">Lat:</span>
                    <span className="font-mono font-medium">
                        {picked.latitude.toFixed(6)}
                    </span>
                    <span className="text-muted-foreground">Lng:</span>
                    <span className="font-mono font-medium">
                        {picked.longitude.toFixed(6)}
                    </span>
                    <button
                        type="button"
                        className="ml-auto text-xs text-muted-foreground underline hover:text-foreground"
                        onClick={handleClear}
                    >
                        Clear
                    </button>
                </div>
            )}
        </div>
    );
}
