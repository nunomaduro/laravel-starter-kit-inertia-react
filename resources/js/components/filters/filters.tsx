import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import {
    ArrowRightIcon,
    ChevronRightIcon,
    FilterIcon,
    Trash2,
    X,
} from 'lucide-react';
import { Fragment, useEffect, useMemo, useRef, useState } from 'react';
import { FilterControl } from './filter-controls';
import type { FilterColumn, FilterValue } from './types';
import { DEFAULT_OPERATOR, OPERATORS } from './types';
import { useFilters } from './use-filters';

interface FiltersProps {
    columns: FilterColumn[];
    serverFilters: Record<string, unknown>;
}

function formatNumericValue(v: string): string {
    const n = Number(v);
    if (Number.isFinite(n)) {
        return n.toLocaleString('fr-TN');
    }
    return v;
}

const BOOL_LABELS: Record<string, string> = { '1': 'Oui', '0': 'Non' };

function formatValueLabel(column: FilterColumn, values: string[]): string {
    if (column.type === 'boolean') {
        return values.map((v) => BOOL_LABELS[v] ?? v).join(', ');
    }
    if (column.type === 'option' && column.options) {
        const labels = values
            .map((v) => column.options!.find((o) => o.value === v)?.label ?? v)
            .slice(0, 3);
        const suffix = values.length > 3 ? ` +${values.length - 3}` : '';
        return labels.join(', ') + suffix;
    }
    if (column.type === 'number') {
        if (values.length === 2) {
            return `${formatNumericValue(values[0])} – ${formatNumericValue(values[1])}`;
        }
        return values.map(formatNumericValue).join(', ');
    }
    if (values.length === 2) {
        return `${values[0]} – ${values[1]}`;
    }
    return values.join(', ');
}

function getOperatorLabel(column: FilterColumn, operator: string): string {
    const opDef = OPERATORS[column.type]?.find((o) => o.value === operator);
    return opDef?.label ?? operator;
}

type PillSection = 'operator' | 'value';

function FilterPill({
    column,
    filterValue,
    openSection,
    onSectionChange,
    onClear,
    onSubmit,
}: {
    column: FilterColumn;
    filterValue: FilterValue;
    openSection: PillSection | null;
    onSectionChange: (section: PillSection | null) => void;
    onClear: () => void;
    onSubmit: (op: string, vals: string[]) => void;
}) {
    const Icon = column.icon;
    const ops = OPERATORS[column.type];

    function handleOperatorSelect(op: string) {
        onSectionChange(null);
        onSubmit(op, filterValue.values);
    }

    return (
        <div className="group/pill flex h-7 items-center rounded-2xl border border-border bg-background text-xs shadow-xs transition-colors duration-150 has-[>[data-slot=clear]:hover]:border-destructive/40 has-[>[data-slot=clear]:hover]:bg-destructive/5">
            <span className="flex items-center gap-1 px-2 font-medium whitespace-nowrap select-none">
                {Icon && <Icon className="size-3.5 stroke-[2.25px]" />}
                <span>{column.label}</span>
            </span>
            <Separator orientation="vertical" />

            {/* Operator section — clickable popover */}
            <Popover
                open={openSection === 'operator'}
                onOpenChange={(open) =>
                    onSectionChange(open ? 'operator' : null)
                }
            >
                <PopoverTrigger asChild>
                    <button
                        type="button"
                        className="h-full px-2 whitespace-nowrap text-muted-foreground transition-colors hover:bg-accent"
                    >
                        {getOperatorLabel(column, filterValue.operator)}
                    </button>
                </PopoverTrigger>
                <PopoverContent
                    className="w-fit origin-(--radix-popover-content-transform-origin) p-0"
                    align="start"
                >
                    <Command loop>
                        <CommandList className="max-h-fit">
                            <CommandGroup heading="Opérateurs">
                                {ops.map((op) => (
                                    <CommandItem
                                        key={op.value}
                                        value={op.value}
                                        onSelect={() =>
                                            handleOperatorSelect(op.value)
                                        }
                                    >
                                        {op.label}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>

            <Separator orientation="vertical" />

            {/* Value section — clickable popover */}
            <Popover
                open={openSection === 'value'}
                onOpenChange={(open) => onSectionChange(open ? 'value' : null)}
            >
                <PopoverTrigger asChild>
                    <button
                        type="button"
                        className={cn(
                            'h-full max-w-[200px] truncate px-2 whitespace-nowrap transition-colors hover:bg-accent',
                            column.type === 'number' && 'tabular-nums',
                        )}
                    >
                        {formatValueLabel(column, filterValue.values)}
                    </button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <FilterControl
                        column={column}
                        value={filterValue}
                        onSubmit={onSubmit}
                        hideOperator
                    />
                </PopoverContent>
            </Popover>

            <Separator orientation="vertical" />
            <button
                type="button"
                data-slot="clear"
                onClick={onClear}
                className="flex h-full items-center rounded-r-2xl px-1.5 transition-colors duration-150 hover:bg-destructive hover:text-white"
            >
                <X className="size-3.5" />
            </button>
        </div>
    );
}

export function Filters({ columns, serverFilters }: FiltersProps) {
    const { activeFilters, setFilter, clearFilter, clearAllFilters } =
        useFilters(serverFilters);

    const [selectorOpen, setSelectorOpen] = useState(false);
    const [selectorColumn, setSelectorColumn] = useState<string | null>(null);
    const [openPill, setOpenPill] = useState<{
        columnId: string;
        section: PillSection;
    } | null>(null);
    const [search, setSearch] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    const hasActiveFilters = Object.keys(activeFilters).length > 0;

    const optionColumns = useMemo(
        () => columns.filter((c) => c.type === 'option' && c.options),
        [columns],
    );

    useEffect(() => {
        if (selectorColumn) {
            inputRef.current?.focus();
        }
    }, [selectorColumn]);

    useEffect(() => {
        if (!selectorOpen) {
            const timeoutId = setTimeout(() => {
                setSelectorColumn(null);
                setSearch('');
            }, 150);
            return () => clearTimeout(timeoutId);
        }
    }, [selectorOpen]);

    function closeAll() {
        setSelectorOpen(false);
        setSelectorColumn(null);
        setOpenPill(null);
    }

    function handleSelectorOpenChange(open: boolean) {
        setSelectorOpen(open);
        if (open) setOpenPill(null);
    }

    function handlePillSectionChange(
        columnId: string,
        section: PillSection | null,
    ) {
        if (section) {
            setSelectorOpen(false);
            setSelectorColumn(null);
            setOpenPill({ columnId, section });
        } else {
            setOpenPill(null);
        }
    }

    function handleQuickOptionToggle(columnId: string, optionValue: string) {
        const current = activeFilters[columnId];
        const operator = current?.operator || DEFAULT_OPERATOR.option;
        const values = new Set(current?.values ?? []);
        if (values.has(optionValue)) values.delete(optionValue);
        else values.add(optionValue);
        setFilter(columnId, operator, Array.from(values));
    }

    const selectedColumn = selectorColumn
        ? columns.find((c) => c.id === selectorColumn)
        : null;

    return (
        <div className="flex flex-wrap items-center gap-1.5">
            <Popover
                open={selectorOpen}
                onOpenChange={handleSelectorOpenChange}
            >
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        className={cn('h-7', hasActiveFilters && 'w-fit !px-2')}
                        onClick={() => setOpenPill(null)}
                    >
                        <FilterIcon className="size-4" />
                        {!hasActiveFilters && <span>Filter</span>}
                    </Button>
                </PopoverTrigger>
                <PopoverContent
                    className="w-fit origin-(--radix-popover-content-transform-origin) p-0"
                    align="start"
                    side="bottom"
                >
                    {selectedColumn ? (
                        <div>
                            <button
                                type="button"
                                onClick={() => setSelectorColumn(null)}
                                className="flex w-full items-center gap-1.5 border-b px-3 py-2 text-sm text-muted-foreground hover:text-foreground"
                            >
                                {selectedColumn.icon && (
                                    <selectedColumn.icon className="size-4 stroke-[2.25px]" />
                                )}
                                <span>{selectedColumn.label}</span>
                            </button>
                            <FilterControl
                                column={selectedColumn}
                                value={activeFilters[selectedColumn.id]}
                                onSubmit={(op, vals) =>
                                    setFilter(selectedColumn.id, op, vals)
                                }
                            />
                        </div>
                    ) : (
                        <Command
                            loop
                            filter={(value, searchTerm, keywords) => {
                                const ext = `${value} ${keywords?.join(' ')}`;
                                return ext
                                    .toLowerCase()
                                    .includes(searchTerm.toLowerCase())
                                    ? 1
                                    : 0;
                            }}
                        >
                            <CommandInput
                                value={search}
                                onValueChange={setSearch}
                                ref={inputRef}
                                placeholder="Rechercher..."
                            />
                            <CommandEmpty>Aucun résultat.</CommandEmpty>
                            <CommandList className="max-h-fit">
                                {hasActiveFilters && (
                                    <>
                                        <CommandGroup>
                                            <CommandItem
                                                value="__clear_all__"
                                                onSelect={() => {
                                                    clearAllFilters();
                                                    setSelectorOpen(false);
                                                }}
                                                className="text-destructive"
                                            >
                                                <div className="flex items-center gap-1.5">
                                                    <Trash2 className="size-4" />
                                                    <span>
                                                        Effacer tous les filtres
                                                    </span>
                                                </div>
                                            </CommandItem>
                                        </CommandGroup>
                                        <Separator />
                                    </>
                                )}
                                <CommandGroup>
                                    {columns.map((col) => {
                                        const isActive =
                                            !!activeFilters[col.id];
                                        return (
                                            <CommandItem
                                                key={col.id}
                                                value={col.id}
                                                keywords={[col.label]}
                                                onSelect={() => {
                                                    setSearch('');
                                                    setSelectorColumn(col.id);
                                                }}
                                                className="group"
                                            >
                                                <div className="flex w-full items-center justify-between">
                                                    <div className="inline-flex items-center gap-1.5">
                                                        {col.icon && (
                                                            <col.icon
                                                                strokeWidth={
                                                                    2.25
                                                                }
                                                                className="size-4"
                                                            />
                                                        )}
                                                        <span
                                                            className={cn(
                                                                isActive &&
                                                                    'font-semibold',
                                                            )}
                                                        >
                                                            {col.label}
                                                        </span>
                                                    </div>
                                                    <ArrowRightIcon className="size-4 opacity-0 group-aria-selected:opacity-100" />
                                                </div>
                                            </CommandItem>
                                        );
                                    })}

                                    {/* Quick search: show option values matching search */}
                                    {search.trim().length >= 2 &&
                                        optionColumns.map((col) => {
                                            const current =
                                                activeFilters[col.id];
                                            const currentValues = new Set(
                                                current?.values ?? [],
                                            );

                                            return (
                                                <Fragment key={`qs-${col.id}`}>
                                                    {col.options!.map((opt) => {
                                                        const checked =
                                                            currentValues.has(
                                                                opt.value,
                                                            );
                                                        return (
                                                            <CommandItem
                                                                key={`${col.id}-${opt.value}`}
                                                                value={
                                                                    opt.value
                                                                }
                                                                keywords={[
                                                                    opt.label,
                                                                    opt.value,
                                                                    col.label,
                                                                ]}
                                                                onSelect={() =>
                                                                    handleQuickOptionToggle(
                                                                        col.id,
                                                                        opt.value,
                                                                    )
                                                                }
                                                                className="group"
                                                            >
                                                                <div className="flex items-center gap-1.5">
                                                                    <Checkbox
                                                                        checked={
                                                                            checked
                                                                        }
                                                                        className="mr-1 opacity-0 group-data-[selected=true]:opacity-100 data-[state=checked]:opacity-100 dark:border-ring"
                                                                    />
                                                                    <div className="flex items-center gap-0.5">
                                                                        <span className="text-muted-foreground">
                                                                            {
                                                                                col.label
                                                                            }
                                                                        </span>
                                                                        <ChevronRightIcon className="size-3.5 text-muted-foreground/75" />
                                                                        <span>
                                                                            {
                                                                                opt.label
                                                                            }
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </CommandItem>
                                                        );
                                                    })}
                                                </Fragment>
                                            );
                                        })}
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    )}
                </PopoverContent>
            </Popover>

            {/* Active filter pills */}
            {Object.entries(activeFilters).map(([columnId, filterValue]) => {
                const col = columns.find((c) => c.id === columnId);
                if (!col) return null;

                const pillOpen =
                    openPill?.columnId === columnId ? openPill.section : null;

                return (
                    <FilterPill
                        key={columnId}
                        column={col}
                        filterValue={filterValue}
                        openSection={pillOpen}
                        onSectionChange={(section) =>
                            handlePillSectionChange(columnId, section)
                        }
                        onClear={() => {
                            clearFilter(columnId);
                            closeAll();
                        }}
                        onSubmit={(op, vals) => setFilter(columnId, op, vals)}
                    />
                );
            })}
        </div>
    );
}
