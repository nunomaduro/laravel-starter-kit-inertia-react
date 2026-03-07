import { useCallback, useState } from 'react';

export function useCopyToClipboard(): [boolean, (text: string) => void] {
    const [copied, setCopied] = useState(false);

    const copy = useCallback((text: string) => {
        if (!navigator?.clipboard) {
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    }, []);

    return [copied, copy];
}
