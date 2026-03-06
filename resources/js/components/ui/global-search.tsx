import { registerShortcut, unregisterShortcut } from '@/lib/keyboard-shortcuts';
import { router } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowUp,
    Clock,
    FileText,
    LifeBuoy,
    Megaphone,
    Search,
    Users,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

interface SearchResult {
    id: number;
    title: string;
    subtitle: string;
    url: string;
    type: string;
}

interface SearchResults {
    users: SearchResult[];
    posts: SearchResult[];
    help_articles: SearchResult[];
    changelog_entries: SearchResult[];
}

const CATEGORY_CONFIG: Record<string, { label: string; icon: React.ElementType }> = {
    users: { label: 'Users', icon: Users },
    posts: { label: 'Posts', icon: FileText },
    help_articles: { label: 'Help Articles', icon: LifeBuoy },
    changelog_entries: { label: 'Changelog', icon: Megaphone },
};

const RECENT_SEARCHES_KEY = 'global_search_recent';
const MAX_RECENT = 10;

function getRecentSearches(): string[] {
    try {
        const stored = localStorage.getItem(RECENT_SEARCHES_KEY);
        return stored ? (JSON.parse(stored) as string[]) : [];
    } catch {
        return [];
    }
}

function saveRecentSearch(query: string): void {
    const recent = getRecentSearches().filter((q) => q !== query);
    recent.unshift(query);
    localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(recent.slice(0, MAX_RECENT)));
}

function removeRecentSearch(query: string): void {
    const recent = getRecentSearches().filter((q) => q !== query);
    localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(recent));
}

interface FlatItem {
    id: string;
    title: string;
    subtitle?: string;
    url: string;
    type: string;
    icon: React.ElementType;
}

function flattenResults(results: SearchResults): FlatItem[] {
    const flat: FlatItem[] = [];
    for (const key of Object.keys(CATEGORY_CONFIG)) {
        const config = CATEGORY_CONFIG[key];
        const items = results[key as keyof SearchResults];
        if (!config || !items) continue;
        for (const item of items) {
            flat.push({
                id: `${item.type}-${item.id}`,
                title: item.title,
                subtitle: item.subtitle,
                url: item.url,
                type: item.type,
                icon: config.icon,
            });
        }
    }
    return flat;
}

export function GlobalSearch(): React.ReactElement | null {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchResults | null>(null);
    const [isSearching, setIsSearching] = useState(false);
    const [activeIndex, setActiveIndex] = useState(-1);
    const [recentSearches, setRecentSearches] = useState<string[]>([]);
    const inputRef = useRef<HTMLInputElement>(null);
    const debounceRef = useRef<ReturnType<typeof setTimeout>>(undefined);
    const abortRef = useRef<AbortController>(undefined);

    const openSearch = useCallback(() => setOpen(true), []);
    const closeSearch = useCallback(() => {
        setOpen(false);
        setQuery('');
        setResults(null);
        setActiveIndex(-1);
    }, []);

    // Register keyboard shortcut
    useEffect(() => {
        registerShortcut({
            keys: 'mod+k',
            description: 'Open global search',
            scope: 'Global',
            action: openSearch,
        });
        return () => unregisterShortcut('mod+k');
    }, [openSearch]);

    // Also listen for custom event from other triggers
    useEffect(() => {
        const handler = () => setOpen(true);
        window.addEventListener('open-global-search', handler);
        return () => window.removeEventListener('open-global-search', handler);
    }, []);

    // Focus input when opened
    useEffect(() => {
        if (open) {
            setRecentSearches(getRecentSearches());
            setTimeout(() => inputRef.current?.focus(), 50);
        }
    }, [open]);

    // Fetch results on query change
    useEffect(() => {
        if (debounceRef.current) clearTimeout(debounceRef.current);
        if (abortRef.current) abortRef.current.abort();

        if (query.length === 0) {
            setResults(null);
            setIsSearching(false);
            setActiveIndex(-1);
            return;
        }

        setIsSearching(true);
        setActiveIndex(-1);

        debounceRef.current = setTimeout(() => {
            const controller = new AbortController();
            abortRef.current = controller;

            fetch(`/search?q=${encodeURIComponent(query)}`, {
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => res.json())
                .then((data: SearchResults) => {
                    if (!controller.signal.aborted) {
                        setResults(data);
                        setIsSearching(false);
                    }
                })
                .catch((err: unknown) => {
                    if (err instanceof Error && err.name !== 'AbortError') {
                        setIsSearching(false);
                    }
                });
        }, 300);

        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current);
        };
    }, [query]);

    const flatItems = results ? flattenResults(results) : [];
    const hasResults = flatItems.length > 0;

    const navigate = useCallback(
        (url: string, searchQuery?: string) => {
            if (searchQuery) saveRecentSearch(searchQuery);
            closeSearch();
            router.visit(url);
        },
        [closeSearch],
    );

    // Keyboard navigation
    useEffect(() => {
        if (!open) return;

        const onKeyDown = (e: KeyboardEvent): void => {
            if (e.key === 'Escape') {
                closeSearch();
                return;
            }

            if (!hasResults) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActiveIndex((i) => (i < flatItems.length - 1 ? i + 1 : 0));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActiveIndex((i) => (i > 0 ? i - 1 : flatItems.length - 1));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && activeIndex < flatItems.length) {
                    const item = flatItems[activeIndex];
                    if (item) navigate(item.url, query);
                } else if (flatItems.length > 0 && flatItems[0]) {
                    navigate(flatItems[0].url, query);
                }
            }
        };

        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [open, hasResults, flatItems, activeIndex, navigate, query, closeSearch]);

    if (!open) return null;

    // Group results by category for display
    const groupedResults: Array<{ key: string; label: string; icon: React.ElementType; items: FlatItem[] }> = [];
    let flatOffset = 0;
    for (const key of Object.keys(CATEGORY_CONFIG)) {
        const config = CATEGORY_CONFIG[key];
        const categoryItems = results?.[key as keyof SearchResults] ?? [];
        if (!config || categoryItems.length === 0) continue;
        const groupItems = categoryItems.map((item) => ({
            id: `${item.type}-${item.id}`,
            title: item.title,
            subtitle: item.subtitle,
            url: item.url,
            type: item.type,
            icon: config.icon,
            flatIdx: flatOffset++,
        }));
        groupedResults.push({ key, label: config.label, icon: config.icon, items: groupItems as FlatItem[] });
    }

    return (
        <div
            className="fixed inset-0 z-50 flex items-start justify-center pt-[10vh]"
            role="dialog"
            aria-modal="true"
            aria-label="Global search"
        >
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-black/50 backdrop-blur-sm"
                onClick={closeSearch}
                aria-hidden="true"
            />

            {/* Panel */}
            <div className="relative z-10 w-full max-w-2xl mx-4 bg-background border rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[70vh]">
                {/* Input row */}
                <div className="flex items-center gap-3 px-4 py-3 border-b">
                    <Search className="size-4 text-muted-foreground shrink-0" />
                    <input
                        ref={inputRef}
                        type="text"
                        className="flex-1 bg-transparent outline-none text-sm placeholder:text-muted-foreground"
                        placeholder="Search..."
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        aria-label="Search query"
                        autoComplete="off"
                        spellCheck={false}
                    />
                    {query && (
                        <button
                            onClick={() => {
                                setQuery('');
                                setResults(null);
                                inputRef.current?.focus();
                            }}
                            className="text-muted-foreground hover:text-foreground transition-colors"
                            aria-label="Clear search"
                        >
                            <X className="size-4" />
                        </button>
                    )}
                    <kbd className="hidden sm:inline-flex items-center gap-1 rounded border bg-muted px-1.5 py-0.5 text-xs text-muted-foreground">
                        Esc
                    </kbd>
                </div>

                {/* Results area */}
                <div className="overflow-y-auto flex-1">
                    {/* Loading state */}
                    {isSearching && (
                        <div className="flex items-center gap-3 px-4 py-6 text-sm text-muted-foreground">
                            <div className="size-4 rounded-full border-2 border-muted-foreground/30 border-t-muted-foreground animate-spin" />
                            Searching…
                        </div>
                    )}

                    {/* No results */}
                    {!isSearching && query && !hasResults && (
                        <div className="px-4 py-8 text-center text-sm text-muted-foreground">
                            No results for &ldquo;{query}&rdquo;
                        </div>
                    )}

                    {/* Results */}
                    {!isSearching && hasResults && (
                        <ul role="listbox" className="py-2">
                            {groupedResults.map((group) => (
                                <li key={group.key}>
                                    <div className="px-4 py-1.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                                        {group.label}
                                    </div>
                                    <ul>
                                        {(group.items as (FlatItem & { flatIdx: number })[]).map((item) => {
                                            const Icon = item.icon;
                                            const isActive = activeIndex === item.flatIdx;
                                            return (
                                                <li
                                                    key={item.id}
                                                    role="option"
                                                    aria-selected={isActive}
                                                >
                                                    <button
                                                        className={`w-full flex items-center gap-3 px-4 py-2.5 text-sm text-left transition-colors ${
                                                            isActive
                                                                ? 'bg-accent text-accent-foreground'
                                                                : 'hover:bg-accent/50'
                                                        }`}
                                                        onClick={() => navigate(item.url, query)}
                                                        onMouseEnter={() => setActiveIndex(item.flatIdx)}
                                                    >
                                                        <Icon className="size-4 text-muted-foreground shrink-0" />
                                                        <div className="min-w-0 flex-1">
                                                            <div className="truncate font-medium">{item.title}</div>
                                                            {item.subtitle && (
                                                                <div className="truncate text-xs text-muted-foreground">
                                                                    {item.subtitle}
                                                                </div>
                                                            )}
                                                        </div>
                                                        <span className="shrink-0 text-xs text-muted-foreground capitalize">
                                                            {item.type.replace(/_/g, ' ')}
                                                        </span>
                                                    </button>
                                                </li>
                                            );
                                        })}
                                    </ul>
                                </li>
                            ))}
                        </ul>
                    )}

                    {/* Recent searches (empty state) */}
                    {!query && recentSearches.length > 0 && (
                        <div className="py-2">
                            <div className="px-4 py-1.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                                Recent searches
                            </div>
                            <ul>
                                {recentSearches.map((recent) => (
                                    <li key={recent} className="flex items-center group">
                                        <button
                                            className="flex-1 flex items-center gap-3 px-4 py-2.5 text-sm text-left hover:bg-accent/50 transition-colors"
                                            onClick={() => setQuery(recent)}
                                        >
                                            <Clock className="size-4 text-muted-foreground shrink-0" />
                                            <span className="truncate">{recent}</span>
                                        </button>
                                        <button
                                            className="pr-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity hover:text-foreground"
                                            onClick={() => {
                                                removeRecentSearch(recent);
                                                setRecentSearches(getRecentSearches());
                                            }}
                                            aria-label={`Remove "${recent}" from recent searches`}
                                        >
                                            <X className="size-3.5" />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {/* Empty state with no recent */}
                    {!query && recentSearches.length === 0 && (
                        <div className="px-4 py-8 text-center text-sm text-muted-foreground">
                            Start typing to search across all content
                        </div>
                    )}
                </div>

                {/* Footer hints */}
                {hasResults && (
                    <div className="flex items-center gap-4 border-t px-4 py-2 text-xs text-muted-foreground">
                        <span className="flex items-center gap-1">
                            <ArrowUp className="size-3" />
                            <ArrowDown className="size-3" />
                            Navigate
                        </span>
                        <span>↵ Open</span>
                        <span className="ml-auto">Esc Close</span>
                    </div>
                )}
            </div>
        </div>
    );
}
