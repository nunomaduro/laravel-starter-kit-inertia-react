import { useEffect, useState } from 'react';

interface WindowSize {
    width: number;
    height: number;
}

export function useWindowSize(): WindowSize {
    const [size, setSize] = useState<WindowSize>({
        width: typeof window !== 'undefined' ? window.innerWidth : 0,
        height: typeof window !== 'undefined' ? window.innerHeight : 0,
    });

    useEffect(() => {
        const handler = () =>
            setSize({ width: window.innerWidth, height: window.innerHeight });
        window.addEventListener('resize', handler);

        return () => window.removeEventListener('resize', handler);
    }, []);

    return size;
}
