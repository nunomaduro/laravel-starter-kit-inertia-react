import { Map, MapControls, MapMarker, MarkerContent } from "@/components/ui/map";
import { cn } from "@/lib/utils";
import { OPEN_FREE_MAP_STYLES } from "./base-map";

export type AnalyticsPoint = {
    id: string;
    longitude: number;
    latitude: number;
    label: string;
    value: number;
    unit?: string;
};

const MOCK_ANALYTICS: AnalyticsPoint[] = [
    { id: "1", longitude: -74.006, latitude: 40.7128, label: "Manhattan", value: 12400, unit: "users" },
    { id: "2", longitude: -73.9442, latitude: 40.6501, label: "Brooklyn", value: 8750, unit: "users" },
    { id: "3", longitude: -73.8648, latitude: 40.7282, label: "Queens", value: 6200, unit: "users" },
    { id: "4", longitude: -73.9442, latitude: 40.8448, label: "Bronx", value: 3100, unit: "users" },
    { id: "5", longitude: -74.1502, latitude: 40.5795, label: "Staten Island", value: 1540, unit: "users" },
];

function getRadius(value: number, max: number): number {
    return Math.max(20, Math.round((value / max) * 60));
}

function getColor(value: number, max: number): string {
    const ratio = value / max;
    if (ratio > 0.7) return "#ef4444";
    if (ratio > 0.4) return "#f59e0b";
    return "#22c55e";
}

type AnalyticsMapProps = {
    points?: AnalyticsPoint[];
    className?: string;
    center?: [number, number];
    zoom?: number;
};

export function AnalyticsMap({
    points = MOCK_ANALYTICS,
    className,
    center = [-74.006, 40.7128],
    zoom = 9,
}: AnalyticsMapProps) {
    const maxValue = Math.max(...points.map((p) => p.value));

    return (
        <div className={cn("relative h-80 w-full overflow-hidden rounded-lg border", className)}>
            <Map
                className="size-full"
                styles={OPEN_FREE_MAP_STYLES}
                center={center}
                zoom={zoom}
            >
                <MapControls />
                {points.map((point) => {
                    const radius = getRadius(point.value, maxValue);
                    const color = getColor(point.value, maxValue);
                    return (
                        <MapMarker
                            key={point.id}
                            longitude={point.longitude}
                            latitude={point.latitude}
                            anchor="center"
                        >
                            <MarkerContent>
                                <div
                                    className="flex cursor-default flex-col items-center justify-center rounded-full border-2 border-white/80 text-center text-white shadow-md transition-transform hover:scale-110"
                                    style={{
                                        width: radius,
                                        height: radius,
                                        backgroundColor: color,
                                        opacity: 0.85,
                                    }}
                                    title={`${point.label}: ${point.value.toLocaleString()} ${point.unit ?? ""}`}
                                >
                                    {radius >= 40 && (
                                        <span className="text-[10px] font-bold leading-none">
                                            {point.value >= 1000
                                                ? `${(point.value / 1000).toFixed(1)}k`
                                                : point.value}
                                        </span>
                                    )}
                                </div>
                            </MarkerContent>
                        </MapMarker>
                    );
                })}
            </Map>

            {/* Legend */}
            <div className="absolute bottom-8 left-3 flex flex-col gap-1 rounded-md border bg-background/90 px-2 py-1.5 text-xs shadow backdrop-blur">
                <span className="font-medium">{points[0]?.unit ?? "value"}</span>
                <div className="flex items-center gap-1.5">
                    <span className="size-3 rounded-full bg-red-500" />
                    <span className="text-muted-foreground">High</span>
                </div>
                <div className="flex items-center gap-1.5">
                    <span className="size-3 rounded-full bg-amber-500" />
                    <span className="text-muted-foreground">Mid</span>
                </div>
                <div className="flex items-center gap-1.5">
                    <span className="size-3 rounded-full bg-green-500" />
                    <span className="text-muted-foreground">Low</span>
                </div>
            </div>
        </div>
    );
}
