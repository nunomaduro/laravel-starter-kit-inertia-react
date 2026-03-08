import { GridIcon, ListIcon, TableIcon } from 'lucide-react';
import * as React from 'react';

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

export type DataViewMode = 'table' | 'grid' | 'list';

export interface DataViewColumn<T> {
    key: keyof T | string;
    label: string;
    render?: (item: T) => React.ReactNode;
    className?: string;
}

export interface DataViewProps<T> {
    items: T[];
    columns?: DataViewColumn<T>[];
    renderGridItem?: (item: T) => React.ReactNode;
    renderListItem?: (item: T) => React.ReactNode;
    keyExtractor: (item: T) => string;
    defaultMode?: DataViewMode;
    searchable?: boolean;
    searchPlaceholder?: string;
    onSearch?: (query: string) => void;
    sortOptions?: { label: string; value: string }[];
    onSort?: (value: string) => void;
    className?: string;
    emptyMessage?: string;
    isLoading?: boolean;
}

function DataView<T>({
    items,
    columns = [],
    renderGridItem,
    renderListItem,
    keyExtractor,
    defaultMode = 'table',
    searchable = false,
    searchPlaceholder = 'Search...',
    onSearch,
    sortOptions,
    onSort,
    className,
    emptyMessage = 'No items found.',
    isLoading = false,
}: DataViewProps<T>) {
    const [mode, setMode] = React.useState<DataViewMode>(defaultMode);
    const [search, setSearch] = React.useState('');

    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearch(e.target.value);
        onSearch?.(e.target.value);
    };

    const availableModes: DataViewMode[] = [];
    if (columns.length > 0) availableModes.push('table');
    if (renderGridItem) availableModes.push('grid');
    if (renderListItem) availableModes.push('list');
    if (availableModes.length === 0) availableModes.push('table');

    const effectiveMode = availableModes.includes(mode)
        ? mode
        : availableModes[0];

    return (
        <div data-slot="data-view" className={cn('space-y-3', className)}>
            {(searchable || sortOptions || availableModes.length > 1) && (
                <div className="flex flex-wrap items-center gap-2">
                    {searchable && (
                        <Input
                            value={search}
                            onChange={handleSearch}
                            placeholder={searchPlaceholder}
                            className="h-8 w-56 text-sm"
                        />
                    )}
                    {sortOptions && onSort && (
                        <Select onValueChange={onSort}>
                            <SelectTrigger className="h-8 w-40 text-sm">
                                <SelectValue placeholder="Sort by..." />
                            </SelectTrigger>
                            <SelectContent>
                                {sortOptions.map((opt) => (
                                    <SelectItem
                                        key={opt.value}
                                        value={opt.value}
                                    >
                                        {opt.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                    {availableModes.length > 1 && (
                        <div className="ml-auto flex items-center gap-1 rounded-md border p-0.5">
                            {availableModes.includes('table') && (
                                <Button
                                    variant={
                                        effectiveMode === 'table'
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    size="sm"
                                    className="h-6 w-6 p-0"
                                    onClick={() => setMode('table')}
                                    aria-label="Table view"
                                >
                                    <TableIcon className="size-3.5" />
                                </Button>
                            )}
                            {availableModes.includes('grid') && (
                                <Button
                                    variant={
                                        effectiveMode === 'grid'
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    size="sm"
                                    className="h-6 w-6 p-0"
                                    onClick={() => setMode('grid')}
                                    aria-label="Grid view"
                                >
                                    <GridIcon className="size-3.5" />
                                </Button>
                            )}
                            {availableModes.includes('list') && (
                                <Button
                                    variant={
                                        effectiveMode === 'list'
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    size="sm"
                                    className="h-6 w-6 p-0"
                                    onClick={() => setMode('list')}
                                    aria-label="List view"
                                >
                                    <ListIcon className="size-3.5" />
                                </Button>
                            )}
                        </div>
                    )}
                </div>
            )}

            {isLoading ? (
                <div className="flex h-40 items-center justify-center text-sm text-muted-foreground">
                    Loading...
                </div>
            ) : items.length === 0 ? (
                <div className="flex h-40 items-center justify-center text-sm text-muted-foreground">
                    {emptyMessage}
                </div>
            ) : effectiveMode === 'table' ? (
                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50">
                            <tr>
                                {columns.map((col) => (
                                    <th
                                        key={String(col.key)}
                                        className={cn(
                                            'px-3 py-2 text-left text-xs font-medium text-muted-foreground',
                                            col.className,
                                        )}
                                    >
                                        {col.label}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {items.map((item) => (
                                <tr
                                    key={keyExtractor(item)}
                                    className="hover:bg-muted/30"
                                >
                                    {columns.map((col) => (
                                        <td
                                            key={String(col.key)}
                                            className={cn(
                                                'px-3 py-2',
                                                col.className,
                                            )}
                                        >
                                            {col.render
                                                ? col.render(item)
                                                : String(
                                                      (
                                                          item as Record<
                                                              string,
                                                              unknown
                                                          >
                                                      )[String(col.key)] ?? '',
                                                  )}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : effectiveMode === 'grid' && renderGridItem ? (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    {items.map((item) => (
                        <React.Fragment key={keyExtractor(item)}>
                            {renderGridItem(item)}
                        </React.Fragment>
                    ))}
                </div>
            ) : renderListItem ? (
                <div className="space-y-1">
                    {items.map((item) => (
                        <React.Fragment key={keyExtractor(item)}>
                            {renderListItem(item)}
                        </React.Fragment>
                    ))}
                </div>
            ) : null}
        </div>
    );
}

export { DataView };
