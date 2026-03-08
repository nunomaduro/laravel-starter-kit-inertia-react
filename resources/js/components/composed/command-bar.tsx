import { BotIcon, ClockIcon, KeyboardIcon, SearchIcon } from 'lucide-react';
import * as React from 'react';

import {
    Command,
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
    CommandShortcut,
} from '@/components/ui/command';
import { Kbd } from '@/components/ui/kbd';
import {
    getShortcuts,
    registerShortcut,
    subscribeToShortcuts,
    unregisterShortcut,
    type ShortcutRegistration,
} from '@/lib/keyboard-shortcuts';

export interface CommandBarAction {
    id: string;
    label: string;
    group?: string;
    icon?: React.ReactNode;
    shortcut?: string;
    onSelect: () => void;
}

export interface RecentItem {
    id: string;
    label: string;
    icon?: React.ReactNode;
    onSelect: () => void;
}

export interface CommandBarProps {
    actions?: CommandBarAction[];
    recentItems?: RecentItem[];
    onAiPrompt?: (query: string) => void;
    aiLabel?: string;
    triggerKey?: string;
    children?: React.ReactNode;
}

function formatShortcutKeys(keys: string): string[] {
    return keys.split('+').map((k) => {
        const map: Record<string, string> = {
            mod: '⌘',
            cmd: '⌘',
            ctrl: 'Ctrl',
            shift: '⇧',
            alt: '⌥',
            meta: '⌘',
        };
        return map[k.toLowerCase()] ?? k.toUpperCase();
    });
}

function CommandBar({
    actions = [],
    recentItems = [],
    onAiPrompt,
    aiLabel = 'Ask AI...',
    triggerKey = 'mod+k',
    children,
}: CommandBarProps) {
    const [open, setOpen] = React.useState(false);
    const [inputValue, setInputValue] = React.useState('');
    const [registeredShortcuts, setRegisteredShortcuts] = React.useState<
        ShortcutRegistration[]
    >(() => getShortcuts());

    React.useEffect(() => {
        return subscribeToShortcuts(() =>
            setRegisteredShortcuts(getShortcuts()),
        );
    }, []);

    React.useEffect(() => {
        registerShortcut({
            keys: triggerKey,
            description: 'Open command bar',
            scope: 'Global',
            action: () => setOpen((o) => !o),
        });
        return () => unregisterShortcut(triggerKey);
    }, [triggerKey]);

    const groupedActions = React.useMemo(() => {
        const map = new Map<string, CommandBarAction[]>();
        for (const action of actions) {
            const group = action.group ?? 'Actions';
            if (!map.has(group)) map.set(group, []);
            map.get(group)!.push(action);
        }
        return Array.from(map.entries());
    }, [actions]);

    const groupedShortcuts = React.useMemo(() => {
        const map = new Map<string, ShortcutRegistration[]>();
        for (const s of registeredShortcuts) {
            if (s.keys === triggerKey) continue;
            const scope = s.scope ?? 'Global';
            if (!map.has(scope)) map.set(scope, []);
            map.get(scope)!.push(s);
        }
        return Array.from(map.entries());
    }, [registeredShortcuts, triggerKey]);

    const handleAiPrompt = () => {
        if (onAiPrompt && inputValue.trim()) {
            onAiPrompt(inputValue.trim());
            setInputValue('');
            setOpen(false);
        }
    };

    return (
        <>
            {children}
            <CommandDialog
                open={open}
                onOpenChange={setOpen}
                showCloseButton={false}
            >
                <Command shouldFilter>
                    <CommandInput
                        placeholder="Search commands or ask AI..."
                        value={inputValue}
                        onValueChange={setInputValue}
                    />
                    <CommandList>
                        <CommandEmpty>
                            <div className="py-6 text-center">
                                <SearchIcon className="mx-auto mb-2 size-8 text-muted-foreground/50" />
                                <p className="text-sm text-muted-foreground">
                                    No results found.
                                </p>
                                {onAiPrompt && inputValue.trim() && (
                                    <button
                                        type="button"
                                        onClick={handleAiPrompt}
                                        className="mx-auto mt-3 flex items-center gap-1.5 rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90"
                                    >
                                        <BotIcon className="size-3.5" />
                                        Ask AI: &ldquo;{inputValue}&rdquo;
                                    </button>
                                )}
                            </div>
                        </CommandEmpty>

                        {onAiPrompt && inputValue.trim() && (
                            <>
                                <CommandGroup heading={aiLabel}>
                                    <CommandItem
                                        onSelect={handleAiPrompt}
                                        className="gap-2"
                                    >
                                        <BotIcon className="size-4 text-primary" />
                                        <span>
                                            Ask AI: &ldquo;{inputValue}&rdquo;
                                        </span>
                                    </CommandItem>
                                </CommandGroup>
                                <CommandSeparator />
                            </>
                        )}

                        {recentItems.length > 0 && (
                            <>
                                <CommandGroup heading="Recent">
                                    {recentItems.map((item) => (
                                        <CommandItem
                                            key={item.id}
                                            onSelect={() => {
                                                item.onSelect();
                                                setOpen(false);
                                            }}
                                            className="gap-2"
                                        >
                                            {item.icon ?? (
                                                <ClockIcon className="size-4 text-muted-foreground" />
                                            )}
                                            <span>{item.label}</span>
                                        </CommandItem>
                                    ))}
                                </CommandGroup>
                                {groupedActions.length > 0 && (
                                    <CommandSeparator />
                                )}
                            </>
                        )}

                        {groupedActions.map(([group, items]) => (
                            <CommandGroup key={group} heading={group}>
                                {items.map((action) => (
                                    <CommandItem
                                        key={action.id}
                                        onSelect={() => {
                                            action.onSelect();
                                            setOpen(false);
                                        }}
                                        className="gap-2"
                                    >
                                        {action.icon}
                                        <span>{action.label}</span>
                                        {action.shortcut && (
                                            <CommandShortcut>
                                                {formatShortcutKeys(
                                                    action.shortcut,
                                                ).join(' ')}
                                            </CommandShortcut>
                                        )}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        ))}

                        {groupedShortcuts.length > 0 && (
                            <>
                                <CommandSeparator />
                                {groupedShortcuts.map(([scope, shortcuts]) => (
                                    <CommandGroup
                                        key={scope}
                                        heading={`Shortcuts — ${scope}`}
                                    >
                                        {shortcuts.map((s) => (
                                            <CommandItem
                                                key={s.keys}
                                                onSelect={() => {
                                                    s.action();
                                                    setOpen(false);
                                                }}
                                                className="gap-2"
                                            >
                                                <KeyboardIcon className="size-4 text-muted-foreground" />
                                                <span>{s.description}</span>
                                                <CommandShortcut>
                                                    <span className="flex items-center gap-0.5">
                                                        {formatShortcutKeys(
                                                            s.keys,
                                                        ).map((key, i) => (
                                                            <Kbd key={i}>
                                                                {key}
                                                            </Kbd>
                                                        ))}
                                                    </span>
                                                </CommandShortcut>
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                ))}
                            </>
                        )}
                    </CommandList>
                </Command>
            </CommandDialog>
        </>
    );
}

export { CommandBar };
