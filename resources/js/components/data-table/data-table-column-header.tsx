import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { ArrowDown, ArrowUp } from 'lucide-react';
import type { DataTableSort } from './types';

interface DataTableColumnHeaderProps {
    label: string;
    children?: React.ReactNode;
    sortable: boolean;
    sorts: DataTableSort[];
    columnId: string;
    onSort: (columnId: string, multi: boolean) => void;
    align?: 'left' | 'right';
}

export function DataTableColumnHeader({
    label,
    children,
    sortable,
    sorts,
    columnId,
    onSort,
    align = 'left',
}: DataTableColumnHeaderProps) {
    const content = children ?? label;

    if (!sortable) {
        return <span className="text-sm font-medium">{content}</span>;
    }

    const sortIndex = sorts.findIndex((s) => s.id === columnId);
    const isActive = sortIndex !== -1;
    const direction = isActive ? sorts[sortIndex].direction : null;
    const isMulti = sorts.length > 1;

    return (
        <Button
            variant="ghost"
            size="sm"
            className={cn('-ml-3 h-8', align === 'right' && '-mr-3 ml-auto')}
            onClick={(e) => onSort(columnId, e.shiftKey)}
        >
            <span>{content}</span>
            {isActive ? (
                <span className="ml-1 inline-flex items-center gap-0.5 text-foreground">
                    {direction === 'desc' ? (
                        <ArrowDown className="h-3.5 w-3.5" />
                    ) : (
                        <ArrowUp className="h-3.5 w-3.5" />
                    )}
                    {isMulti && (
                        <span className="text-[9px] leading-none font-bold tabular-nums">
                            {sortIndex + 1}
                        </span>
                    )}
                </span>
            ) : null}
        </Button>
    );
}
