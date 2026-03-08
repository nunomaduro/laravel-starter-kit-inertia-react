import {
    Map,
    MapControls,
    MapMarker,
    MarkerPopup,
    MarkerTooltip,
} from '@/components/ui/map';
import { cn } from '@/lib/utils';
import { useState } from 'react';
import { OPEN_FREE_MAP_STYLES } from './base-map';

export type MapMarkerData = {
    id: string;
    longitude: number;
    latitude: number;
    label?: string;
    description?: string;
    color?: string;
};

const MOCK_MARKERS: MapMarkerData[] = [
    {
        id: '1',
        longitude: -74.006,
        latitude: 40.7128,
        label: 'New York',
        description: 'The Big Apple',
        color: '#3b82f6',
    },
    {
        id: '2',
        longitude: -73.9857,
        latitude: 40.7484,
        label: 'Empire State',
        description: 'Iconic skyscraper',
        color: '#ef4444',
    },
    {
        id: '3',
        longitude: -73.9654,
        latitude: 40.7829,
        label: 'Central Park',
        description: 'Urban green space',
        color: '#22c55e',
    },
    {
        id: '4',
        longitude: -74.0447,
        latitude: 40.6892,
        label: 'Statue of Liberty',
        description: 'Lady Liberty',
        color: '#f59e0b',
    },
    {
        id: '5',
        longitude: -73.9442,
        latitude: 40.6501,
        label: 'Brooklyn',
        description: 'Borough of culture',
        color: '#8b5cf6',
    },
];

type MarkersMapProps = {
    markers?: MapMarkerData[];
    className?: string;
    center?: [number, number];
    zoom?: number;
};

export function MarkersMap({
    markers = MOCK_MARKERS,
    className,
    center = [-74.006, 40.7128],
    zoom = 10,
}: MarkersMapProps) {
    const [selectedId, setSelectedId] = useState<string | null>(null);

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
                {markers.map((marker) => (
                    <MapMarker
                        key={marker.id}
                        longitude={marker.longitude}
                        latitude={marker.latitude}
                        color={marker.color}
                        onClick={() =>
                            setSelectedId(
                                marker.id === selectedId ? null : marker.id,
                            )
                        }
                    >
                        {marker.label && (
                            <MarkerTooltip>{marker.label}</MarkerTooltip>
                        )}
                        {marker.description && selectedId === marker.id && (
                            <MarkerPopup>
                                <div className="min-w-[120px] p-1">
                                    {marker.label && (
                                        <p className="text-sm font-medium">
                                            {marker.label}
                                        </p>
                                    )}
                                    <p className="text-xs text-muted-foreground">
                                        {marker.description}
                                    </p>
                                </div>
                            </MarkerPopup>
                        )}
                    </MapMarker>
                ))}
            </Map>
        </div>
    );
}
