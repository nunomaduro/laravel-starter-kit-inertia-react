import { useEffect, useRef } from 'react';

export function useInterval(callback: () => void, delay: number | null): void {
    const savedCallback = useRef(callback);
    savedCallback.current = callback;

    useEffect(() => {
        if (delay === null) {
            return;
        }
        const id = setInterval(() => savedCallback.current(), delay);

        return () => clearInterval(id);
    }, [delay]);
}
