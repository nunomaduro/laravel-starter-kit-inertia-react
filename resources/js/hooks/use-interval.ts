import { useEffect, useRef } from 'react';

export function useInterval(callback: () => void, delay: number | null): void {
    const savedCallbackRef = useRef(callback);
    savedCallbackRef.current = callback;

    useEffect(() => {
        if (delay === null) {
            return;
        }
        const id = setInterval(() => savedCallbackRef.current(), delay);

        return () => clearInterval(id);
    }, [delay]);
}
