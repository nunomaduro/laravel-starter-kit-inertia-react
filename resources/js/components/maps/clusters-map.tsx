import { Map, MapControls, MapClusterLayer } from "@/components/ui/map";
import { cn } from "@/lib/utils";
import { OPEN_FREE_MAP_STYLES } from "./base-map";

type ClusterPoint = {
    longitude: number;
    latitude: number;
    [key: string]: unknown;
};

function toGeoJSON(points: ClusterPoint[]): GeoJSON.FeatureCollection<GeoJSON.Point> {
    return {
        type: "FeatureCollection",
        features: points.map((p, i) => ({
            type: "Feature",
            geometry: { type: "Point" as const, coordinates: [p.longitude, p.latitude] },
            properties: { id: i, ...p },
        })),
    };
}

const MOCK_POINTS: ClusterPoint[] = Array.from({ length: 80 }, (_, i) => ({
    longitude: -74.006 + (Math.sin(i * 1.3) * 0.4),
    latitude: 40.7128 + (Math.cos(i * 1.7) * 0.25),
    name: `Point ${i + 1}`,
}));

type ClustersMapProps = {
    points?: ClusterPoint[];
    className?: string;
    center?: [number, number];
    zoom?: number;
    clusterMaxZoom?: number;
    clusterRadius?: number;
};

export function ClustersMap({
    points = MOCK_POINTS,
    className,
    center = [-74.006, 40.7128],
    zoom = 9,
    clusterMaxZoom = 14,
    clusterRadius = 50,
}: ClustersMapProps) {
    const data = toGeoJSON(points);

    return (
        <div className={cn("relative h-80 w-full overflow-hidden rounded-lg border", className)}>
            <Map
                className="size-full"
                styles={OPEN_FREE_MAP_STYLES}
                center={center}
                zoom={zoom}
            >
                <MapControls />
                <MapClusterLayer
                    data={data}
                    clusterMaxZoom={clusterMaxZoom}
                    clusterRadius={clusterRadius}
                    clusterColors={["#22c55e", "#f59e0b", "#ef4444"]}
                    clusterThresholds={[20, 50]}
                    pointColor="#3b82f6"
                />
            </Map>
        </div>
    );
}
