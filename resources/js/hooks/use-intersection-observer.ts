import { type RefObject, useEffect, useState } from 'react';

export function useIntersectionObserver(ref: RefObject<Element | null>, options?: IntersectionObserverInit): IntersectionObserverEntry | null {
    const [entry, setEntry] = useState<IntersectionObserverEntry | null>(null);

    useEffect(() => {
        const el = ref.current;
        if (!el) {
            return;
        }
        const observer = new IntersectionObserver(([e]) => setEntry(e ?? null), options);
        observer.observe(el);

        return () => observer.disconnect();
    }, [ref, options]);

    return entry;
}
