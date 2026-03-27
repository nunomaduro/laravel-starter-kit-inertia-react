import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type DateInfo = {
    available: number;
    total: number;
    blocked: boolean;
};

type AvailabilityCalendarProps = {
    dates: Record<string, DateInfo>;
    month: string;
    onMonthChange: (month: string) => void;
    onDateClick?: (date: string) => void;
};

const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function parseMonth(month: string): { year: number; monthIndex: number } {
    const [yearStr, monthStr] = month.split('-');
    return { year: Number(yearStr), monthIndex: Number(monthStr) - 1 };
}

function formatMonth(year: number, monthIndex: number): string {
    return `${year}-${String(monthIndex + 1).padStart(2, '0')}`;
}

export function AvailabilityCalendar({ dates, month, onMonthChange, onDateClick }: AvailabilityCalendarProps) {
    const { year, monthIndex } = parseMonth(month);
    const firstDay = new Date(year, monthIndex, 1).getDay();
    const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();

    const monthLabel = new Date(year, monthIndex).toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    });

    const goToPrev = () => {
        const d = new Date(year, monthIndex - 1, 1);
        onMonthChange(formatMonth(d.getFullYear(), d.getMonth()));
    };

    const goToNext = () => {
        const d = new Date(year, monthIndex + 1, 1);
        onMonthChange(formatMonth(d.getFullYear(), d.getMonth()));
    };

    const cells: (null | { day: number; dateStr: string; info: DateInfo | undefined })[] = [];
    for (let i = 0; i < firstDay; i++) {
        cells.push(null);
    }
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        cells.push({ day, dateStr, info: dates[dateStr] });
    }

    const getCellColor = (info: DateInfo | undefined): string => {
        if (!info) {
            return 'bg-neutral-50 dark:bg-neutral-900';
        }
        if (info.blocked) {
            return 'bg-neutral-200 dark:bg-neutral-700';
        }
        if (info.available === 0) {
            return 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300';
        }
        if (info.available < info.total) {
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
        }
        return 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300';
    };

    return (
        <div>
            <div className="mb-4 flex items-center justify-between">
                <Button variant="ghost" size="icon" onClick={goToPrev}>
                    <ChevronLeft className="size-4" />
                </Button>
                <h3 className="font-semibold">{monthLabel}</h3>
                <Button variant="ghost" size="icon" onClick={goToNext}>
                    <ChevronRight className="size-4" />
                </Button>
            </div>

            <div className="grid grid-cols-7 gap-1">
                {DAY_LABELS.map((label) => (
                    <div key={label} className="py-1 text-center text-xs font-medium text-muted-foreground">
                        {label}
                    </div>
                ))}

                {cells.map((cell, idx) => {
                    if (!cell) {
                        return <div key={`empty-${idx}`} />;
                    }

                    return (
                        <button
                            key={cell.dateStr}
                            type="button"
                            className={cn(
                                'flex flex-col items-center rounded-md p-1 text-xs transition-colors',
                                getCellColor(cell.info),
                                onDateClick && 'cursor-pointer hover:ring-2 hover:ring-primary/50',
                            )}
                            onClick={() => onDateClick?.(cell.dateStr)}
                            disabled={!onDateClick}
                        >
                            <span className="font-medium">{cell.day}</span>
                            {cell.info && !cell.info.blocked && (
                                <span className="text-[10px]">
                                    {cell.info.available}/{cell.info.total}
                                </span>
                            )}
                            {cell.info?.blocked && <span className="text-[10px]">N/A</span>}
                        </button>
                    );
                })}
            </div>

            <div className="mt-3 flex flex-wrap gap-3 text-xs text-muted-foreground">
                <span className="flex items-center gap-1">
                    <span className="size-3 rounded-sm bg-green-100 dark:bg-green-950" /> Available
                </span>
                <span className="flex items-center gap-1">
                    <span className="size-3 rounded-sm bg-amber-100 dark:bg-amber-950" /> Partial
                </span>
                <span className="flex items-center gap-1">
                    <span className="size-3 rounded-sm bg-red-100 dark:bg-red-950" /> Full
                </span>
                <span className="flex items-center gap-1">
                    <span className="size-3 rounded-sm bg-neutral-200 dark:bg-neutral-700" /> Blocked
                </span>
            </div>
        </div>
    );
}
