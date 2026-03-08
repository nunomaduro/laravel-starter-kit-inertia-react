import { Calendar } from '@/components/ui/calendar';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { isEqual } from 'date-fns';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { DateRange } from 'react-day-picker';
import {
    DEFAULT_OPERATOR,
    OPERATORS,
    type FilterColumn,
    type FilterValue,
} from './types';

interface FilterControlProps {
    column: FilterColumn;
    value?: FilterValue;
    onSubmit: (operator: string, values: string[]) => void;
    hideOperator?: boolean;
}

function OperatorSelect({
    type,
    value,
    onChange,
}: {
    type: FilterColumn['type'];
    value: string;
    onChange: (op: string) => void;
}) {
    const ops = OPERATORS[type];
    if (ops.length <= 1) return null;

    return (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger className="h-8 text-xs">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {ops.map((op) => (
                    <SelectItem key={op.value} value={op.value}>
                        {op.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

export function OptionFilter({
    column,
    value,
    onSubmit,
    hideOperator,
}: FilterControlProps) {
    const [search, setSearch] = useState('');
    const [operator, setOperator] = useState(
        value?.operator || DEFAULT_OPERATOR.option,
    );
    const selected = new Set(value?.values ?? []);

    const filteredOptions = useMemo(() => {
        if (!column.options) return [];
        if (!search) return column.options;
        const s = search.toLowerCase();
        return column.options.filter((o) => o.label.toLowerCase().includes(s));
    }, [column.options, search]);

    function toggle(optionValue: string) {
        const next = new Set(selected);
        if (next.has(optionValue)) next.delete(optionValue);
        else next.add(optionValue);
        onSubmit(operator, Array.from(next));
    }

    function handleOperatorChange(op: string) {
        setOperator(op);
        if (selected.size > 0) {
            onSubmit(op, Array.from(selected));
        }
    }

    const threshold = column.searchThreshold ?? 5;
    const showSearch = (column.options?.length ?? 0) >= threshold;

    return (
        <div className="flex w-[260px] flex-col gap-2 p-2">
            {!hideOperator && (
                <OperatorSelect
                    type="option"
                    value={operator}
                    onChange={handleOperatorChange}
                />
            )}
            {showSearch && (
                <div className="relative">
                    <Search className="absolute top-2 left-2 h-4 w-4 text-muted-foreground" />
                    <Input
                        placeholder="Rechercher..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="h-8 pl-8 text-sm"
                    />
                </div>
            )}
            <div className="flex max-h-[200px] flex-col gap-0.5 overflow-y-auto">
                {filteredOptions.map((opt) => (
                    <label
                        key={opt.value}
                        className="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent"
                    >
                        <Checkbox
                            checked={selected.has(opt.value)}
                            onCheckedChange={() => toggle(opt.value)}
                        />
                        {opt.label}
                    </label>
                ))}
                {filteredOptions.length === 0 && (
                    <p className="px-2 py-1 text-sm text-muted-foreground">
                        Aucun résultat.
                    </p>
                )}
            </div>
        </div>
    );
}

export function NumberFilter({
    value,
    onSubmit,
    hideOperator,
}: FilterControlProps) {
    const [operator, setOperator] = useState(
        value?.operator || DEFAULT_OPERATOR.number,
    );
    const [val1, setVal1] = useState(value?.values[0] ?? '');
    const [val2, setVal2] = useState(value?.values[1] ?? '');

    const isRange =
        OPERATORS.number.find((o) => o.value === operator)?.multi ?? false;

    function submit() {
        const values = isRange && val2 ? [val1, val2] : val1 ? [val1] : [];
        onSubmit(operator, values);
    }

    function handleOperatorChange(op: string) {
        setOperator(op);
        if (val1) {
            const multi =
                OPERATORS.number.find((o) => o.value === op)?.multi ?? false;
            const values = multi && val2 ? [val1, val2] : [val1];
            onSubmit(op, values);
        }
    }

    function handleKeyDown(e: React.KeyboardEvent) {
        if (e.key === 'Enter') submit();
    }

    return (
        <div className="flex w-[260px] flex-col gap-2 p-2">
            {!hideOperator && (
                <OperatorSelect
                    type="number"
                    value={operator}
                    onChange={handleOperatorChange}
                />
            )}
            <div className={isRange ? 'grid grid-cols-2 gap-2' : ''}>
                <Input
                    type="number"
                    placeholder={isRange ? 'Min' : 'Valeur'}
                    value={val1}
                    onChange={(e) => setVal1(e.target.value)}
                    onKeyDown={handleKeyDown}
                    autoFocus
                    className="h-8 text-sm"
                />
                {isRange && (
                    <Input
                        type="number"
                        placeholder="Max"
                        value={val2}
                        onChange={(e) => setVal2(e.target.value)}
                        onKeyDown={handleKeyDown}
                        className="h-8 text-sm"
                    />
                )}
            </div>
            <p className="text-xs text-muted-foreground">
                Appuyez sur Entrée pour filtrer
            </p>
        </div>
    );
}

export function DateFilter({
    value,
    onSubmit,
    hideOperator,
}: FilterControlProps) {
    const [operator, setOperator] = useState(
        value?.operator || DEFAULT_OPERATOR.date,
    );
    const isRange =
        OPERATORS.date.find((o) => o.value === operator)?.multi ?? false;

    const [date, setDate] = useState<DateRange | undefined>(() => {
        if (!value?.values.length) return undefined;
        return {
            from: new Date(value.values[0]),
            to: value.values[1] ? new Date(value.values[1]) : undefined,
        };
    });

    function fmt(d: Date): string {
        return d.toISOString().slice(0, 10);
    }

    function handleDateChange(range: DateRange | undefined) {
        setDate(range);
        if (!range?.from) return;

        if (isRange) {
            if (range.from && range.to && !isEqual(range.from, range.to)) {
                onSubmit(operator, [fmt(range.from), fmt(range.to)]);
            }
        } else {
            onSubmit(operator, [fmt(range.from)]);
        }
    }

    function handleOperatorChange(op: string) {
        setOperator(op);
        if (date?.from) {
            const multi =
                OPERATORS.date.find((o) => o.value === op)?.multi ?? false;
            if (multi && date.to) {
                onSubmit(op, [fmt(date.from), fmt(date.to)]);
            } else if (!multi) {
                onSubmit(op, [fmt(date.from)]);
            }
        }
    }

    return (
        <div className="flex flex-col gap-2 p-2">
            {!hideOperator && (
                <OperatorSelect
                    type="date"
                    value={operator}
                    onChange={handleOperatorChange}
                />
            )}
            <Calendar
                mode="range"
                selected={date}
                onSelect={handleDateChange}
                numberOfMonths={1}
                initialFocus
            />
        </div>
    );
}

export function TextFilter({
    value,
    onSubmit,
    hideOperator,
}: FilterControlProps) {
    const [operator, setOperator] = useState(
        value?.operator || DEFAULT_OPERATOR.text,
    );
    const [text, setText] = useState(value?.values[0] ?? '');

    function submit() {
        if (text) onSubmit(operator, [text]);
        else onSubmit(operator, []);
    }

    function handleOperatorChange(op: string) {
        setOperator(op);
        if (text) onSubmit(op, [text]);
    }

    function handleKeyDown(e: React.KeyboardEvent) {
        if (e.key === 'Enter') submit();
    }

    return (
        <div className="flex w-[260px] flex-col gap-2 p-2">
            {!hideOperator && (
                <OperatorSelect
                    type="text"
                    value={operator}
                    onChange={handleOperatorChange}
                />
            )}
            <Input
                placeholder="Rechercher..."
                value={text}
                onChange={(e) => setText(e.target.value)}
                onKeyDown={handleKeyDown}
                autoFocus
                className="h-8 text-sm"
            />
            <p className="text-xs text-muted-foreground">
                Appuyez sur Entrée pour filtrer
            </p>
        </div>
    );
}

const BOOL_OPTIONS = [
    { label: 'Oui', value: '1' },
    { label: 'Non', value: '0' },
];

/** A standalone min/max range filter without an operator dropdown. */
export function RangeFilter({ value, onSubmit }: FilterControlProps) {
    const [min, setMin] = useState(value?.values[0] ?? '');
    const [max, setMax] = useState(value?.values[1] ?? '');

    function submit(nextMin: string, nextMax: string) {
        const values: string[] = [];
        if (nextMin !== '') values.push(nextMin);
        if (nextMax !== '') values.push(nextMax);
        onSubmit('between', values);
    }

    function handleKeyDown(e: React.KeyboardEvent) {
        if (e.key === 'Enter') submit(min, max);
    }

    return (
        <div className="flex w-[260px] flex-col gap-2 p-2">
            <div className="grid grid-cols-2 gap-2">
                <Input
                    type="number"
                    placeholder="Min"
                    value={min}
                    onChange={(e) => setMin(e.target.value)}
                    onBlur={() => submit(min, max)}
                    onKeyDown={handleKeyDown}
                    autoFocus
                    className="h-8 text-sm"
                />
                <Input
                    type="number"
                    placeholder="Max"
                    value={max}
                    onChange={(e) => setMax(e.target.value)}
                    onBlur={() => submit(min, max)}
                    onKeyDown={handleKeyDown}
                    className="h-8 text-sm"
                />
            </div>
        </div>
    );
}

/** Single-select radio filter for option columns. */
export function RadioFilter({ column, value, onSubmit }: FilterControlProps) {
    const selected = value?.values[0] ?? '';
    const options = column.options ?? [];

    function handleChange(optionValue: string) {
        if (selected === optionValue) {
            onSubmit('eq', []);
        } else {
            onSubmit('eq', [optionValue]);
        }
    }

    return (
        <div className="flex w-[260px] flex-col gap-0.5 p-2">
            {options.map((opt) => (
                <label
                    key={opt.value}
                    className="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent"
                >
                    <input
                        type="radio"
                        name={`radio-filter-${column.id}`}
                        value={opt.value}
                        checked={selected === opt.value}
                        onChange={() => handleChange(opt.value)}
                        className="h-4 w-4 accent-primary"
                    />
                    {opt.label}
                </label>
            ))}
            {options.length === 0 && (
                <p className="px-2 py-1 text-sm text-muted-foreground">
                    No options.
                </p>
            )}
        </div>
    );
}

export function FilterControl({
    column,
    value,
    onSubmit,
    hideOperator,
}: FilterControlProps) {
    switch (column.type) {
        case 'boolean':
            return (
                <OptionFilter
                    column={{
                        ...column,
                        type: 'option',
                        options: BOOL_OPTIONS,
                        searchThreshold: 999,
                    }}
                    value={value}
                    onSubmit={onSubmit}
                    hideOperator
                />
            );
        case 'option':
            return (
                <OptionFilter
                    column={column}
                    value={value}
                    onSubmit={onSubmit}
                    hideOperator={hideOperator}
                />
            );
        case 'number':
            return (
                <NumberFilter
                    column={column}
                    value={value}
                    onSubmit={onSubmit}
                    hideOperator={hideOperator}
                />
            );
        case 'date':
            return (
                <DateFilter
                    column={column}
                    value={value}
                    onSubmit={onSubmit}
                    hideOperator={hideOperator}
                />
            );
        case 'text':
            return (
                <TextFilter
                    column={column}
                    value={value}
                    onSubmit={onSubmit}
                    hideOperator={hideOperator}
                />
            );
        case 'range':
            return (
                <RangeFilter
                    column={column}
                    value={value}
                    onSubmit={onSubmit}
                />
            );
        case 'radio':
            return (
                <RadioFilter
                    column={column}
                    value={value}
                    onSubmit={onSubmit}
                />
            );
        default:
            return null;
    }
}
