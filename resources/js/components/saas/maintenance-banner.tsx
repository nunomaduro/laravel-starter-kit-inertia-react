import { usePage } from '@inertiajs/react';
import { WrenchIcon, XIcon } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

interface MaintenanceInfo {
    message: string;
    scheduledAt?: string | null;
}

interface MaintenanceBannerProps {
    className?: string;
}

function MaintenanceBanner({ className }: MaintenanceBannerProps) {
    const props = usePage<SharedData>().props;
    const maintenance = props.maintenance as MaintenanceInfo | null | undefined;

    const [dismissed, setDismissed] = React.useState(false);

    if (!maintenance || dismissed) return null;

    const formattedDate = maintenance.scheduledAt
        ? new Intl.DateTimeFormat(undefined, {
              dateStyle: 'medium',
              timeStyle: 'short',
          }).format(new Date(maintenance.scheduledAt))
        : null;

    return (
        <div
            className={cn(
                'relative flex items-center justify-center gap-2 bg-yellow-400 px-4 py-2 text-sm font-medium text-yellow-950',
                className,
            )}
        >
            <WrenchIcon className="size-4 shrink-0" />
            <span>
                {maintenance.message}
                {formattedDate && (
                    <span className="ml-1 opacity-75">
                        — scheduled {formattedDate}
                    </span>
                )}
            </span>
            <button
                onClick={() => setDismissed(true)}
                aria-label="Dismiss maintenance notice"
                className="absolute top-1/2 right-3 -translate-y-1/2 rounded p-1 opacity-70 hover:opacity-100 focus:ring-2 focus:ring-yellow-700 focus:outline-none"
            >
                <XIcon className="size-4" />
            </button>
        </div>
    );
}

export { MaintenanceBanner };
export type { MaintenanceBannerProps, MaintenanceInfo };
