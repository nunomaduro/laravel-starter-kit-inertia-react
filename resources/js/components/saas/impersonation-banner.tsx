import { usePage } from '@inertiajs/react';

import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

interface ImpersonationBannerProps {
    /** URL to stop impersonation. Defaults to /admin/impersonate/leave */
    leaveUrl?: string;
    className?: string;
}

function ImpersonationBanner({
    leaveUrl = '/admin/impersonate/leave',
    className,
}: ImpersonationBannerProps) {
    const { auth } = usePage<SharedData>().props;

    const authAny = auth as unknown as Record<string, unknown>;
    const impersonating = authAny.impersonating as boolean | undefined;
    const impersonatedUser = authAny.impersonated_user as
        | { name?: string }
        | undefined;

    if (!impersonating) return null;

    const userName =
        impersonatedUser?.name ?? auth.user?.name ?? 'another user';

    return (
        <div
            className={cn(
                'relative flex items-center justify-center gap-3 bg-amber-500 px-4 py-2 text-sm font-medium text-amber-950',
                className,
            )}
        >
            <span>
                You are viewing as <strong>{userName}</strong>
            </span>
            <a
                href={leaveUrl}
                className="rounded border border-amber-700 px-2 py-0.5 text-xs font-semibold hover:bg-amber-600 focus:ring-2 focus:ring-amber-700 focus:outline-none"
            >
                Stop Impersonating
            </a>
        </div>
    );
}

export { ImpersonationBanner };
export type { ImpersonationBannerProps };
