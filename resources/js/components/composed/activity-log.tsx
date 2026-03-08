import { FilterIcon } from 'lucide-react';
import * as React from 'react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';

export interface ActivityEntry {
    id: string;
    actor?: {
        name: string;
        avatar?: string;
        initials?: string;
    };
    action: string;
    target?: string;
    targetUrl?: string;
    description?: string;
    timestamp: Date | string;
    type?: string;
    metadata?: Record<string, string | number | boolean>;
    icon?: React.ReactNode;
}

export interface ActivityLogProps {
    entries: ActivityEntry[];
    types?: string[];
    showFilters?: boolean;
    onFilterChange?: (filters: { search?: string; type?: string }) => void;
    isLoading?: boolean;
    className?: string;
    emptyMessage?: string;
    maxHeight?: number;
}

function formatTimestamp(ts: Date | string): string {
    const date = ts instanceof Date ? ts : new Date(ts);
    const diff = Date.now() - date.getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs}h ago`;
    const days = Math.floor(hrs / 24);
    if (days < 7) return `${days}d ago`;
    return date.toLocaleString();
}

function ActivityEntryRow({ entry }: { entry: ActivityEntry }) {
    return (
        <div data-slot="activity-entry" className="flex gap-3 py-3">
            <div className="relative flex shrink-0 flex-col items-center">
                {entry.actor ? (
                    <Avatar className="size-7">
                        <AvatarImage
                            src={entry.actor.avatar}
                            alt={entry.actor.name}
                        />
                        <AvatarFallback className="text-[10px]">
                            {entry.actor.initials ??
                                entry.actor.name
                                    .split(' ')
                                    .map((n) => n[0])
                                    .join('')
                                    .slice(0, 2)
                                    .toUpperCase()}
                        </AvatarFallback>
                    </Avatar>
                ) : entry.icon ? (
                    <div className="flex size-7 items-center justify-center rounded-full bg-muted text-muted-foreground">
                        {entry.icon}
                    </div>
                ) : (
                    <div className="size-7 rounded-full bg-muted" />
                )}
                <div className="mt-1 w-px flex-1 bg-border" />
            </div>
            <div className="min-w-0 flex-1 pb-3">
                <div className="flex flex-wrap items-center gap-1 text-sm">
                    {entry.actor && (
                        <span className="font-medium">{entry.actor.name}</span>
                    )}
                    <span className="text-muted-foreground">
                        {entry.action}
                    </span>
                    {entry.target && (
                        <>
                            {entry.targetUrl ? (
                                <a
                                    href={entry.targetUrl}
                                    className="font-medium text-primary hover:underline"
                                >
                                    {entry.target}
                                </a>
                            ) : (
                                <span className="font-medium">
                                    {entry.target}
                                </span>
                            )}
                        </>
                    )}
                    {entry.type && (
                        <Badge
                            variant="secondary"
                            className="px-1.5 py-0 text-[10px]"
                        >
                            {entry.type}
                        </Badge>
                    )}
                </div>
                {entry.description && (
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        {entry.description}
                    </p>
                )}
                {entry.metadata && Object.keys(entry.metadata).length > 0 && (
                    <div className="mt-1.5 flex flex-wrap gap-2">
                        {Object.entries(entry.metadata).map(([key, val]) => (
                            <span
                                key={key}
                                className="rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground"
                            >
                                {key}:{' '}
                                <span className="font-medium text-foreground">
                                    {String(val)}
                                </span>
                            </span>
                        ))}
                    </div>
                )}
                <time
                    dateTime={
                        entry.timestamp instanceof Date
                            ? entry.timestamp.toISOString()
                            : entry.timestamp
                    }
                    className="mt-1 block text-[10px] text-muted-foreground"
                >
                    {formatTimestamp(entry.timestamp)}
                </time>
            </div>
        </div>
    );
}

function ActivityLog({
    entries,
    types = [],
    showFilters = false,
    onFilterChange,
    isLoading = false,
    className,
    emptyMessage = 'No activity yet.',
    maxHeight,
}: ActivityLogProps) {
    const [search, setSearch] = React.useState('');
    const [typeFilter, setTypeFilter] = React.useState('__all__');

    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearch(e.target.value);
        onFilterChange?.({
            search: e.target.value,
            type: typeFilter === '__all__' ? undefined : typeFilter,
        });
    };

    const handleTypeFilter = (value: string) => {
        setTypeFilter(value);
        onFilterChange?.({
            search,
            type: value === '__all__' ? undefined : value,
        });
    };

    const filtered = entries.filter((entry) => {
        const matchesSearch =
            !search ||
            entry.action.toLowerCase().includes(search.toLowerCase()) ||
            entry.actor?.name.toLowerCase().includes(search.toLowerCase()) ||
            entry.target?.toLowerCase().includes(search.toLowerCase());
        const matchesType =
            typeFilter === '__all__' ||
            !entry.type ||
            entry.type === typeFilter;
        return matchesSearch && matchesType;
    });

    return (
        <div data-slot="activity-log" className={cn('space-y-3', className)}>
            {showFilters && (
                <div className="flex flex-wrap items-center gap-2">
                    <FilterIcon className="size-4 text-muted-foreground" />
                    <Input
                        value={search}
                        onChange={handleSearch}
                        placeholder="Search activity..."
                        className="h-8 w-48 text-sm"
                    />
                    {types.length > 0 && (
                        <Select
                            value={typeFilter}
                            onValueChange={handleTypeFilter}
                        >
                            <SelectTrigger className="h-8 w-36 text-sm">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__all__">
                                    All types
                                </SelectItem>
                                {types.map((t) => (
                                    <SelectItem key={t} value={t}>
                                        {t}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                </div>
            )}

            {isLoading ? (
                <div className="flex h-32 items-center justify-center text-sm text-muted-foreground">
                    Loading...
                </div>
            ) : filtered.length === 0 ? (
                <div className="flex h-32 items-center justify-center text-sm text-muted-foreground">
                    {emptyMessage}
                </div>
            ) : (
                <div
                    className={cn(
                        'overflow-y-auto',
                        maxHeight && `max-h-[${maxHeight}px]`,
                    )}
                    style={
                        maxHeight ? { maxHeight: `${maxHeight}px` } : undefined
                    }
                >
                    <div className="divide-y divide-transparent">
                        {filtered.map((entry) => (
                            <ActivityEntryRow key={entry.id} entry={entry} />
                        ))}
                    </div>
                </div>
            )}

            {!isLoading && filtered.length > 0 && (
                <div className="text-center">
                    <Button
                        variant="ghost"
                        size="sm"
                        className="text-xs text-muted-foreground"
                    >
                        {filtered.length}{' '}
                        {filtered.length === 1 ? 'entry' : 'entries'}
                    </Button>
                </div>
            )}
        </div>
    );
}

export { ActivityLog };
