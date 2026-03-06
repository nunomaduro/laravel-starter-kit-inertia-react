import { useEffect } from 'react';

export interface ShortcutRegistration {
    keys: string;
    description: string;
    action: () => void;
    scope?: string;
}

const registry = new Map<string, ShortcutRegistration>();
const changeListeners = new Set<() => void>();

function notifyListeners(): void {
    changeListeners.forEach((fn) => fn());
}

export function registerShortcut(shortcut: ShortcutRegistration): void {
    registry.set(shortcut.keys, shortcut);
    notifyListeners();
}

export function unregisterShortcut(keys: string): void {
    registry.delete(keys);
    notifyListeners();
}

export function getShortcuts(): ShortcutRegistration[] {
    return Array.from(registry.values());
}

export function subscribeToShortcuts(listener: () => void): () => void {
    changeListeners.add(listener);
    return () => changeListeners.delete(listener);
}

interface ParsedKeys {
    key: string;
    ctrl: boolean;
    meta: boolean;
    shift: boolean;
    alt: boolean;
    mod: boolean;
}

function parseKeys(keys: string): ParsedKeys {
    const parts = keys.toLowerCase().split('+');
    return {
        key: parts[parts.length - 1],
        ctrl: parts.includes('ctrl'),
        meta: parts.includes('cmd') || parts.includes('meta'),
        shift: parts.includes('shift'),
        alt: parts.includes('alt'),
        mod: parts.includes('mod'),
    };
}

function matchesEvent(parsed: ParsedKeys, event: KeyboardEvent): boolean {
    const isMac = navigator.platform.toUpperCase().includes('MAC');
    const modKey = isMac ? event.metaKey : event.ctrlKey;

    if (parsed.mod && !modKey) return false;
    if (parsed.meta && !event.metaKey) return false;
    if (parsed.ctrl && !event.ctrlKey) return false;
    if (parsed.shift && !event.shiftKey) return false;
    if (parsed.alt && !event.altKey) return false;

    const eventKey = event.key.toLowerCase();
    const targetKey = parsed.key;

    return eventKey === targetKey;
}

export function useKeyboardShortcut(keys: string, handler: () => void): void {
    useEffect(() => {
        const parsed = parseKeys(keys);

        const onKeyDown = (event: KeyboardEvent): void => {
            const target = event.target as HTMLElement;
            const isEditable =
                target.tagName === 'INPUT' ||
                target.tagName === 'TEXTAREA' ||
                target.isContentEditable;

            // Allow single-character shortcuts only outside editable elements
            const hasMod =
                parsed.mod || parsed.ctrl || parsed.meta || parsed.alt;
            if (!hasMod && isEditable) return;

            if (matchesEvent(parsed, event)) {
                event.preventDefault();
                handler();
            }
        };

        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [keys, handler]);
}
