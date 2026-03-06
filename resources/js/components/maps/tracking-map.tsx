import { useEffect, useRef, useState } from "react";
import { Map, MapControls, MapMarker, MapRoute, MarkerContent, MarkerTooltip } from "@/components/ui/map";
import { cn } from "@/lib/utils";
import { OPEN_FREE_MAP_STYLES } from "./base-map";

export type TrackedAsset = {
    id: string;
    label: string;
    color?: string;
    /** Current position [longitude, latitude] */
    position: [number, number];
    /** Speed in km/h */
    speed?: number;
    /** Heading in degrees (0 = north) */
    heading?: number;
};

const MOCK_ASSETS: TrackedAsset[] = [
    { id: "truck-1", label: "Truck 01", color: "#3b82f6", position: [-74.006, 40.7128], speed: 42, heading: 45 },
    { id: "truck-2", label: "Truck 02", color: "#ef4444", position: [-73.98, 40.74], speed: 28, heading: 180 },
    { id: "van-1", label: "Van A", color: "#22c55e", position: [-74.02, 40.69], speed: 0, heading: 0 },
];

/** Slightly move an asset each tick to simulate movement */
function simulateMove(asset: TrackedAsset): TrackedAsset {
    if (asset.speed === 0) return asset;
    const rad = ((asset.heading ?? 0) * Math.PI) / 180;
    const delta = 0.0003;
    return {
        ...asset,
        position: [
            asset.position[0] + Math.sin(rad) * delta,
            asset.position[1] + Math.cos(rad) * delta,
        ],
    };
}

type TrackingMapProps = {
    assets?: TrackedAsset[];
    className?: string;
    center?: [number, number];
    zoom?: number;
    /** Simulate real-time movement updates */
    simulate?: boolean;
    /** Update interval in ms when simulate=true */
    intervalMs?: number;
};

export function TrackingMap({
    assets: initialAssets = MOCK_ASSETS,
    className,
    center = [-74.006, 40.7128],
    zoom = 11,
    simulate = true,
    intervalMs = 1500,
}: TrackingMapProps) {
    const [assets, setAssets] = useState<TrackedAsset[]>(initialAssets);
    const [trails, setTrails] = useState<Record<string, [number, number][]>>(
        Object.fromEntries(initialAssets.map((a) => [a.id, [a.position]]))
    );
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    useEffect(() => {
        if (!simulate) return;
        intervalRef.current = setInterval(() => {
            setAssets((prev) => {
                const next = prev.map(simulateMove);
                setTrails((t) =>
                    Object.fromEntries(
                        next.map((a) => [a.id, [...(t[a.id] ?? []), a.position].slice(-30)])
                    )
                );
                return next;
            });
        }, intervalMs);
        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current);
        };
    }, [simulate, intervalMs]);

    return (
        <div className={cn("relative h-80 w-full overflow-hidden rounded-lg border", className)}>
            <Map
                className="size-full"
                styles={OPEN_FREE_MAP_STYLES}
                center={center}
                zoom={zoom}
            >
                <MapControls />

                {/* Trail lines */}
                {assets.map((asset) => {
                    const trail = trails[asset.id] ?? [];
                    if (trail.length < 2) return null;
                    return (
                        <MapRoute
                            key={`trail-${asset.id}`}
                            id={`trail-${asset.id}`}
                            coordinates={trail}
                            color={asset.color ?? "#3b82f6"}
                            width={2}
                            opacity={0.5}
                            dashArray={[2, 3]}
                        />
                    );
                })}

                {/* Asset markers */}
                {assets.map((asset) => (
                    <MapMarker
                        key={asset.id}
                        longitude={asset.position[0]}
                        latitude={asset.position[1]}
                        anchor="center"
                    >
                        <MarkerContent>
                            <div
                                className="relative flex items-center justify-center"
                                style={{ transform: `rotate(${asset.heading ?? 0}deg)` }}
                            >
                                <div
                                    className="size-4 rounded-full border-2 border-white shadow-md"
                                    style={{ backgroundColor: asset.color ?? "#3b82f6" }}
                                />
                                {/* Direction arrow */}
                                {(asset.speed ?? 0) > 0 && (
                                    <div
                                        className="absolute -top-1.5 left-1/2 h-2 w-0.5 -translate-x-1/2"
                                        style={{ backgroundColor: asset.color ?? "#3b82f6" }}
                                    />
                                )}
                            </div>
                        </MarkerContent>
                        <MarkerTooltip>
                            <span className="font-medium">{asset.label}</span>
                            {asset.speed !== undefined && (
                                <span className="ml-1 text-muted-foreground">
                                    {asset.speed} km/h
                                </span>
                            )}
                        </MarkerTooltip>
                    </MapMarker>
                ))}
            </Map>

            {/* Status panel */}
            <div className="absolute right-3 top-3 flex flex-col gap-1 rounded-md border bg-background/90 px-2 py-1.5 text-xs shadow backdrop-blur">
                {assets.map((asset) => (
                    <div key={asset.id} className="flex items-center gap-1.5">
                        <span
                            className="size-2 rounded-full"
                            style={{ backgroundColor: asset.color ?? "#3b82f6" }}
                        />
                        <span className="font-medium">{asset.label}</span>
                        <span className="text-muted-foreground">
                            {asset.speed === 0 ? "Stopped" : `${asset.speed} km/h`}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}
