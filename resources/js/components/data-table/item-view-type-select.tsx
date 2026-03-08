import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { LayoutGrid, List, Table2 } from 'lucide-react';

export type ItemViewType = 'table' | 'grid' | 'list';

interface ItemViewTypeSelectProps {
    value: ItemViewType;
    onChange: (value: ItemViewType) => void;
    className?: string;
    /** Which views to show (default: all three) */
    views?: ItemViewType[];
}

const VIEW_CONFIG: Record<
    ItemViewType,
    { icon: React.ComponentType<{ className?: string }>; label: string }
> = {
    table: { icon: Table2, label: 'Table view' },
    grid: { icon: LayoutGrid, label: 'Grid view' },
    list: { icon: List, label: 'List view' },
};

export function ItemViewTypeSelect({
    value,
    onChange,
    className,
    views = ['table', 'grid', 'list'],
}: ItemViewTypeSelectProps) {
    return (
        <div className={cn('flex items-center rounded-md border', className)}>
            {views.map((viewType) => {
                const { icon: Icon, label } = VIEW_CONFIG[viewType];
                const isActive = value === viewType;
                return (
                    <Button
                        key={viewType}
                        variant="ghost"
                        size="icon"
                        className={cn(
                            'h-8 w-8 rounded-none first:rounded-l-md last:rounded-r-md',
                            isActive && 'bg-accent text-accent-foreground',
                        )}
                        onClick={() => onChange(viewType)}
                        aria-label={label}
                        aria-pressed={isActive}
                    >
                        <Icon className="h-4 w-4" />
                    </Button>
                );
            })}
        </div>
    );
}
