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
    const fromId = `filter-${parameterName}-from`;
    const toId = `filter-${parameterName}-to`;
    const selectId = `filter-${parameterName}`;

    return (
        <div className="rounded-lg border bg-card p-4">
            <span className="mb-2 block text-sm font-medium text-foreground" id={`filter-label-${parameterName}`}>
                {label}
            </span>
            {filterType === 'date_range' ? (
                <div className="flex items-center gap-2" role="group" aria-labelledby={`filter-label-${parameterName}`}>
                    <label htmlFor={fromId} className="sr-only">From date</label>
                    <input
                        id={fromId}
                        type="date"
                        name={`${parameterName}_from`}
                        defaultValue={defaultFrom}
                        className="rounded-md border bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                    <span className="text-sm text-muted-foreground">to</span>
                    <label htmlFor={toId} className="sr-only">To date</label>
                    <input
                        id={toId}
                        type="date"
                        name={`${parameterName}_to`}
                        defaultValue={defaultTo}
                        className="rounded-md border bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                </div>
            ) : (
                <>
                    <label htmlFor={selectId} className="sr-only">{label}</label>
                    <select
                        id={selectId}
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
                </>
            )}
        </div>
    );
}
