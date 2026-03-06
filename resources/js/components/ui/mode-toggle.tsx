import { router, usePage } from '@inertiajs/react';
import { Monitor, Moon, Sun } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

type Mode = 'dark' | 'light' | 'system';

const options: { value: Mode; icon: LucideIcon; label: string }[] = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
];

const prefersDark = (): boolean =>
    typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;

const applyMode = (mode: Mode): void => {
    const isDark = mode === 'dark' || (mode === 'system' && prefersDark());
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
};

export function ModeToggle({ className }: { className?: string }) {
    const { props } = usePage<SharedData>();
    const serverMode = (props.theme?.userMode ?? 'system') as Mode;

    const [mode, setMode] = useState<Mode>(serverMode);

    useEffect(() => {
        applyMode(mode);
    }, [mode]);

    // Re-apply when system preference changes (only relevant in 'system' mode)
    useEffect(() => {
        if (mode !== 'system') {
            return;
        }

        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = () => applyMode('system');
        mq.addEventListener('change', handler);

        return () => mq.removeEventListener('change', handler);
    }, [mode]);

    const handleChange = useCallback(
        (value: string) => {
            if (!value) {
                return;
            }

            const newMode = value as Mode;
            setMode(newMode);
            applyMode(newMode);

            router.patch(
                '/user/preferences',
                { theme_mode: newMode },
                { preserveState: true, preserveScroll: true },
            );
        },
        [],
    );

    return (
        <ToggleGroup
            type="single"
            value={mode}
            onValueChange={handleChange}
            className={cn('inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800', className)}
        >
            {options.map(({ value, icon: Icon, label }) => (
                <ToggleGroupItem
                    key={value}
                    value={value}
                    aria-label={label}
                    data-pan={`mode-toggle-${value}`}
                    className={cn(
                        'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                        mode === value
                            ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                            : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                    )}
                >
                    <Icon className="-ml-1 h-4 w-4" />
                    <span className="ml-1.5 text-sm">{label}</span>
                </ToggleGroupItem>
            ))}
        </ToggleGroup>
    );
}
