import { useEffect, useState } from 'react';

/**
 * Returns `true` when the user has requested reduced motion via OS/browser settings.
 * Animated components should disable or reduce animations when this returns `true`.
 */
export function useReducedMotion(): boolean {
    const [reducedMotion, setReducedMotion] = useState<boolean>(() => {
        if (typeof window === 'undefined') {
            return false;
        }

        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    });

    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

        const handler = (event: MediaQueryListEvent) => {
            setReducedMotion(event.matches);
        };

        mediaQuery.addEventListener('change', handler);

        return () => {
            mediaQuery.removeEventListener('change', handler);
        };
    }, []);

    return reducedMotion;
}
