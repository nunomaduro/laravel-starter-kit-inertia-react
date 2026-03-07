import { useCallback, useState } from 'react';

export function useToggle(initial = false): [boolean, () => void, (v: boolean) => void] {
    const [value, setValue] = useState(initial);

    const toggle = useCallback(() => setValue((v) => !v), []);
    const set = useCallback((v: boolean) => setValue(v), []);

    return [value, toggle, set];
}
