import { WifiOff, X } from 'lucide-react';
import { useEffect, useState } from 'react';

import { cn } from '@/lib/utils';
import { useOnline } from '@/hooks/use-online';
import { useReducedMotion } from '@/hooks/use-reduced-motion';

export function OfflineBanner() {
    const online = useOnline();
    const reducedMotion = useReducedMotion();
    const [visible, setVisible] = useState(!online);
    const [dismissed, setDismissed] = useState(false);

    useEffect(() => {
        if (!online) {
            setVisible(true);
            setDismissed(false);

            return;
        }

        // Auto-dismiss 3 seconds after reconnecting
        const timer = setTimeout(() => setVisible(false), 3000);

        return () => clearTimeout(timer);
    }, [online]);

    if (!visible || dismissed) {
        return null;
    }

    return (
        <div
            role="status"
            aria-live="polite"
            className={cn(
                'fixed inset-x-0 top-0 z-[100] flex items-center justify-between gap-3 px-4 py-2.5',
                online ? 'bg-success/90 text-success-foreground' : 'bg-destructive text-destructive-foreground',
                !reducedMotion && 'animate-in slide-in-from-top duration-300',
            )}
        >
            <div className="flex items-center gap-2 text-sm font-medium">
                <WifiOff className="h-4 w-4 shrink-0" />
                {online ? 'Connection restored.' : 'You are offline. Some features may not be available.'}
            </div>
            <button
                type="button"
                onClick={() => setDismissed(true)}
                aria-label="Dismiss"
                className="rounded p-0.5 opacity-80 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-white/50"
            >
                <X className="h-4 w-4" />
            </button>
        </div>
    );
}
