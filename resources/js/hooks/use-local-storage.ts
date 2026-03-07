import { useCallback, useState } from 'react';

type Setter<T> = (value: T | ((prev: T) => T)) => void;

export function useLocalStorage<T>(key: string, initialValue: T): [T, Setter<T>, () => void] {
    const [storedValue, setStoredValue] = useState<T>(() => {
        try {
            const item = window.localStorage.getItem(key);

            return item !== null ? (JSON.parse(item) as T) : initialValue;
        } catch {
            return initialValue;
        }
    });

    const setValue: Setter<T> = useCallback(
        (value) => {
            setStoredValue((prev) => {
                const next = typeof value === 'function' ? (value as (prev: T) => T)(prev) : value;
                try {
                    window.localStorage.setItem(key, JSON.stringify(next));
                } catch {
                    // ignore
                }

                return next;
            });
        },
        [key],
    );

    const removeValue = useCallback(() => {
        try {
            window.localStorage.removeItem(key);
        } catch {
            // ignore
        }
        setStoredValue(initialValue);
    }, [key, initialValue]);

    return [storedValue, setValue, removeValue];
}
