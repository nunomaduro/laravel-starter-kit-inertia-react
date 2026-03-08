import { Map, MapControls, MapMarker, MapRoute } from '@/components/ui/map';
import { cn } from '@/lib/utils';
import { OPEN_FREE_MAP_STYLES } from './base-map';

export type RouteData = {
    id: string;
    label?: string;
    coordinates: [number, number][];
    color?: string;
    width?: number;
    dashed?: boolean;
};

const MOCK_ROUTES: RouteData[] = [
    {
        id: 'route-1',
        label: 'Route A',
        color: '#3b82f6',
        width: 4,
        coordinates: [
            [-74.006, 40.7128],
            [-73.9857, 40.7484],
            [-73.9654, 40.7829],
        ],
    },
    {
        id: 'route-2',
        label: 'Route B',
        color: '#ef4444',
        width: 3,
        dashed: true,
        coordinates: [
            [-74.006, 40.7128],
            [-74.0447, 40.6892],
            [-73.9442, 40.6501],
        ],
    },
];

type RoutesMapProps = {
    routes?: RouteData[];
    className?: string;
    center?: [number, number];
    zoom?: number;
};

export function RoutesMap({
    routes = MOCK_ROUTES,
    className,
    center = [-74.006, 40.7128],
    zoom = 10,
}: RoutesMapProps) {
    return (
        <div
            className={cn(
                'relative h-80 w-full overflow-hidden rounded-lg border',
                className,
            )}
        >
            <Map
                className="size-full"
                styles={OPEN_FREE_MAP_STYLES}
                center={center}
                zoom={zoom}
            >
                <MapControls />
                {routes.map((route) => (
                    <MapRoute
                        key={route.id}
                        id={route.id}
                        coordinates={route.coordinates}
                        color={route.color ?? '#3b82f6'}
                        width={route.width ?? 3}
                        dashArray={route.dashed ? [4, 4] : undefined}
                    />
                ))}
                {routes.flatMap((route) => {
                    const start = route.coordinates[0];
                    const end = route.coordinates[route.coordinates.length - 1];
                    return [
                        <MapMarker
                            key={`${route.id}-start`}
                            longitude={start[0]}
                            latitude={start[1]}
                            color="#22c55e"
                        >
                            {null}
                        </MapMarker>,
                        <MapMarker
                            key={`${route.id}-end`}
                            longitude={end[0]}
                            latitude={end[1]}
                            color="#ef4444"
                        >
                            {null}
                        </MapMarker>,
                    ];
                })}
            </Map>
        </div>
    );
}
