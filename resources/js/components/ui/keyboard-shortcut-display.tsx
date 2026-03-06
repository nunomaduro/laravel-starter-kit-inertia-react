import * as React from 'react';

import { Kbd } from '@/components/ui/kbd';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import {
    getShortcuts,
    subscribeToShortcuts,
    useKeyboardShortcut,
    type ShortcutRegistration,
} from '@/lib/keyboard-shortcuts';
import { cn } from '@/lib/utils';

function formatKeys(keys: string): string[] {
    return keys.split('+').map((k) => {
        const map: Record<string, string> = {
            mod: '⌘',
            cmd: '⌘',
            ctrl: 'Ctrl',
            shift: '⇧',
            alt: '⌥',
            meta: '⌘',
            enter: '↵',
            escape: 'Esc',
            backspace: '⌫',
            delete: 'Del',
            tab: 'Tab',
            arrowup: '↑',
            arrowdown: '↓',
            arrowleft: '←',
            arrowright: '→',
        };
        const lower = k.toLowerCase();
        return map[lower] ?? k.toUpperCase();
    });
}

function ShortcutRow({ shortcut }: { shortcut: ShortcutRegistration }) {
    const keyParts = formatKeys(shortcut.keys);
    return (
        <div className="flex items-center justify-between py-1.5">
            <span className="text-sm text-foreground">
                {shortcut.description}
            </span>
            <div className="flex items-center gap-0.5">
                {keyParts.map((part, i) => (
                    <React.Fragment key={i}>
                        {i > 0 && (
                            <span className="mx-0.5 text-xs text-muted-foreground">
                                +
                            </span>
                        )}
                        <Kbd>{part}</Kbd>
                    </React.Fragment>
                ))}
            </div>
        </div>
    );
}

interface GroupedShortcuts {
    scope: string;
    shortcuts: ShortcutRegistration[];
}

function groupShortcuts(shortcuts: ShortcutRegistration[]): GroupedShortcuts[] {
    const map = new Map<string, ShortcutRegistration[]>();
    for (const s of shortcuts) {
        const scope = s.scope ?? 'Global';
        if (!map.has(scope)) map.set(scope, []);
        map.get(scope)!.push(s);
    }
    return Array.from(map.entries()).map(([scope, items]) => ({
        scope,
        shortcuts: items,
    }));
}

export interface KeyboardShortcutDisplayProps {
    className?: string;
}

export function KeyboardShortcutDisplay({
    className,
}: KeyboardShortcutDisplayProps) {
    const [open, setOpen] = React.useState(false);
    const [shortcuts, setShortcuts] = React.useState<ShortcutRegistration[]>(
        () => getShortcuts(),
    );

    React.useEffect(() => {
        return subscribeToShortcuts(() => setShortcuts(getShortcuts()));
    }, []);

    useKeyboardShortcut('?', () => setOpen((o) => !o));

    const groups = groupShortcuts(shortcuts);

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetContent
                side="right"
                className={cn('w-80 overflow-y-auto', className)}
            >
                <SheetHeader>
                    <SheetTitle>Keyboard Shortcuts</SheetTitle>
                </SheetHeader>
                <div className="mt-4 space-y-6 px-4 pb-4">
                    {groups.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No shortcuts registered.
                        </p>
                    )}
                    {groups.map((group) => (
                        <div key={group.scope}>
                            <h3 className="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                {group.scope}
                            </h3>
                            <div className="divide-y divide-border">
                                {group.shortcuts.map((s) => (
                                    <ShortcutRow key={s.keys} shortcut={s} />
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </SheetContent>
        </Sheet>
    );
}
