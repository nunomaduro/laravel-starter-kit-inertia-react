export interface MapMarker {
    lat: number;
    lng: number;
    label: string;
    color?: string;
}

export interface MapBlockProps {
    title: string;
    dataSource: string;
    height: number;
    data?: MapMarker[];
}

export function MapBlock({ title, height, data }: MapBlockProps) {
    const markers = data ?? [];

    return (
        <div className="rounded-lg border bg-card p-4">
            {title && <h3 className="mb-3 text-lg font-semibold">{title}</h3>}
            <div
                className="relative overflow-hidden rounded-md border bg-muted"
                style={{ height: `${height}px` }}
            >
                {markers.length === 0 ? (
                    <div className="flex h-full items-center justify-center">
                        <p className="text-sm text-muted-foreground">
                            No location data. Connect a data source with lat/lng
                            fields.
                        </p>
                    </div>
                ) : (
                    <div className="relative h-full w-full">
                        <div className="absolute inset-0 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900" />
                        <svg
                            viewBox="0 0 400 300"
                            className="absolute inset-0 h-full w-full"
                            preserveAspectRatio="xMidYMid meet"
                        >
                            {markers.map((marker, i) => {
                                const x =
                                    ((marker.lng + 180) / 360) * 400;
                                const y =
                                    ((90 - marker.lat) / 180) * 300;
                                return (
                                    <g key={i} role="img" aria-label={`${marker.label} at ${marker.lat.toFixed(2)}, ${marker.lng.toFixed(2)}`}>
                                        <title>{marker.label}</title>
                                        <circle
                                            cx={x}
                                            cy={y}
                                            r={6}
                                            fill={
                                                marker.color ??
                                                'var(--color-primary)'
                                            }
                                            opacity={0.8}
                                        />
                                        <text
                                            x={x}
                                            y={y - 10}
                                            textAnchor="middle"
                                            className="fill-foreground text-[8px]"
                                        >
                                            {marker.label}
                                        </text>
                                    </g>
                                );
                            })}
                        </svg>
                    </div>
                )}
            </div>
        </div>
    );
}
