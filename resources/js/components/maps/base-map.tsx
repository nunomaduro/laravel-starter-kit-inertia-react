import { type ReactNode } from "react";
import { Map, MapControls } from "@/components/ui/map";
import { cn } from "@/lib/utils";

export const OPEN_FREE_MAP_STYLES = {
    light: "https://tiles.openfreemap.org/styles/liberty",
    dark: "https://tiles.openfreemap.org/styles/dark",
};

type BaseMapProps = {
    children?: ReactNode;
    className?: string;
    /** Initial center [longitude, latitude] */
    center?: [number, number];
    /** Initial zoom level */
    zoom?: number;
    /** Show navigation controls */
    showControls?: boolean;
};

export function BaseMap({
    children,
    className,
    center = [-74.006, 40.7128],
    zoom = 10,
    showControls = true,
}: BaseMapProps) {
    return (
        <div className={cn("relative h-80 w-full overflow-hidden rounded-lg border", className)}>
            <Map
                className="size-full"
                styles={OPEN_FREE_MAP_STYLES}
                center={center}
                zoom={zoom}
            >
                {showControls && <MapControls />}
                {children}
            </Map>
        </div>
    );
}
