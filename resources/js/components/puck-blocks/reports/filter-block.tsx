export interface FilterOption {
    label: string;
    value: string;
}

export interface FilterBlockProps {
    label: string;
    filterType: 'date_range' | 'dropdown';
    parameterName: string;
    options: FilterOption[];
    defaultFrom?: string;
    defaultTo?: string;
    defaultValue?: string;
}

export function FilterBlock({
    label,
    filterType,
    parameterName,
    options,
    defaultFrom,
    defaultTo,
    defaultValue,
}: FilterBlockProps) {
    return (
        <div className="rounded-lg border bg-card p-4">
            <label className="mb-2 block text-sm font-medium text-foreground">
                {label}
            </label>
            {filterType === 'date_range' ? (
                <div className="flex items-center gap-2">
                    <input
                        type="date"
                        name={`${parameterName}_from`}
                        defaultValue={defaultFrom}
                        className="rounded-md border bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                    <span className="text-sm text-muted-foreground">to</span>
                    <input
                        type="date"
                        name={`${parameterName}_to`}
                        defaultValue={defaultTo}
                        className="rounded-md border bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                </div>
            ) : (
                <select
                    name={parameterName}
                    defaultValue={defaultValue}
                    className="w-full max-w-xs rounded-md border bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                >
                    <option value="">All</option>
                    {options.map((opt) => (
                        <option key={opt.value} value={opt.value}>
                            {opt.label}
                        </option>
                    ))}
                </select>
            )}
        </div>
    );
}
