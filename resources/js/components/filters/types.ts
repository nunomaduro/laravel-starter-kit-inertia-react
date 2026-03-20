import type { LucideIcon } from "lucide-react";
import type { DataTableTranslations } from "../data-table/i18n";

export type FilterType = "text" | "number" | "date" | "option" | "boolean";

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
    labelKey: keyof DataTableTranslations;
    multi: boolean;
}

export const OPERATORS: Record<FilterType, OperatorDef[]> = {
    text: [
        { value: "contains", labelKey: "opContains", multi: false },
        { value: "eq", labelKey: "opExact", multi: false },
    ],
    number: [
        { value: "eq", labelKey: "opEquals", multi: false },
        { value: "neq", labelKey: "opNotEquals", multi: false },
        { value: "gt", labelKey: "opGreaterThan", multi: false },
        { value: "gte", labelKey: "opGreaterOrEqual", multi: false },
        { value: "lt", labelKey: "opLessThan", multi: false },
        { value: "lte", labelKey: "opLessOrEqual", multi: false },
        { value: "between", labelKey: "opBetween", multi: true },
    ],
    date: [
        { value: "eq", labelKey: "opOnDate", multi: false },
        { value: "before", labelKey: "opBefore", multi: false },
        { value: "after", labelKey: "opAfter", multi: false },
        { value: "between", labelKey: "opBetween", multi: true },
    ],
    option: [
        { value: "in", labelKey: "opIs", multi: false },
        { value: "not_in", labelKey: "opIsNot", multi: false },
    ],
    boolean: [
        { value: "eq", labelKey: "opIs", multi: false },
    ],
};

export const DEFAULT_OPERATOR: Record<FilterType, string> = {
    text: "contains",
    number: "eq",
    date: "eq",
    option: "in",
    boolean: "eq",
};
