import type { LucideIcon } from 'lucide-react';

export type FilterType = 'text' | 'number' | 'date' | 'option' | 'boolean';

export interface FilterColumn {
    id: string;
    label: string;
    type: FilterType;
    icon?: LucideIcon;
    options?: { label: string; value: string }[];
    searchThreshold?: number;
}

export interface FilterValue {
    operator: string;
    values: string[];
}

export type ActiveFilters = Record<string, FilterValue>;

export interface OperatorDef {
    value: string;
    label: string;
    multi: boolean;
}

export const OPERATORS: Record<FilterType, OperatorDef[]> = {
    text: [
        { value: 'contains', label: 'contient', multi: false },
        { value: 'eq', label: 'est exactement', multi: false },
    ],
    number: [
        { value: 'eq', label: '=', multi: false },
        { value: 'neq', label: '≠', multi: false },
        { value: 'gt', label: '>', multi: false },
        { value: 'gte', label: '≥', multi: false },
        { value: 'lt', label: '<', multi: false },
        { value: 'lte', label: '≤', multi: false },
        { value: 'between', label: 'entre', multi: true },
    ],
    date: [
        { value: 'eq', label: 'est le', multi: false },
        { value: 'before', label: 'avant le', multi: false },
        { value: 'after', label: 'après le', multi: false },
        { value: 'between', label: 'entre', multi: true },
    ],
    option: [
        { value: 'in', label: 'est', multi: false },
        { value: 'not_in', label: "n'est pas", multi: false },
    ],
    boolean: [{ value: 'eq', label: 'est', multi: false }],
};

export const DEFAULT_OPERATOR: Record<FilterType, string> = {
    text: 'contains',
    number: 'eq',
    date: 'eq',
    option: 'in',
    boolean: 'eq',
};
