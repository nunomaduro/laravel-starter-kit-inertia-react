import * as React from 'react';
import { ChevronsUpDownIcon, CheckIcon, CpuIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';

export interface ModelOption {
    id: string;
    label: string;
    provider?: string;
    contextWindow?: number;
    /** Custom description line. */
    description?: string;
}

export interface ModelSelectorProps {
    /** Currently selected model id. */
    value?: string;
    /** Callback when model changes. */
    onChange?: (modelId: string) => void;
    /** Available models to choose from. */
    models?: ModelOption[];
    /** Disable the selector. */
    disabled?: boolean;
    placeholder?: string;
    className?: string;
}

const DEFAULT_MODELS: ModelOption[] = [
    { id: 'claude-sonnet-4-6', label: 'Claude Sonnet 4.6', provider: 'Anthropic', contextWindow: 200000 },
    { id: 'claude-opus-4-6', label: 'Claude Opus 4.6', provider: 'Anthropic', contextWindow: 200000 },
    { id: 'claude-haiku-4-5', label: 'Claude Haiku 4.5', provider: 'Anthropic', contextWindow: 200000 },
    { id: 'gpt-4o', label: 'GPT-4o', provider: 'OpenAI', contextWindow: 128000 },
    { id: 'gpt-4o-mini', label: 'GPT-4o Mini', provider: 'OpenAI', contextWindow: 128000 },
    { id: 'gemini-2.0-flash', label: 'Gemini 2.0 Flash', provider: 'Google', contextWindow: 1000000 },
];

/**
 * Combobox-style model selector for picking an AI model.
 * Works standalone or connected to `AssistantRuntimeProvider` via `setConfig`.
 */
export function ModelSelector({
    value,
    onChange,
    models = DEFAULT_MODELS,
    disabled = false,
    placeholder = 'Select model…',
    className,
}: ModelSelectorProps) {
    const [open, setOpen] = React.useState(false);

    const selected = models.find((m) => m.id === value);

    const grouped = React.useMemo(() => {
        const map = new Map<string, ModelOption[]>();
        for (const m of models) {
            const key = m.provider ?? 'Other';
            if (!map.has(key)) map.set(key, []);
            map.get(key)!.push(m);
        }
        return map;
    }, [models]);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    disabled={disabled}
                    className={cn('justify-between gap-2 min-w-[180px]', className)}
                >
                    <span className="flex items-center gap-1.5 truncate">
                        <CpuIcon className="size-3.5 shrink-0 text-muted-foreground" />
                        {selected?.label ?? <span className="text-muted-foreground">{placeholder}</span>}
                    </span>
                    <ChevronsUpDownIcon className="size-3.5 shrink-0 opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-[280px] p-0" align="start">
                <Command>
                    <CommandInput placeholder="Search models…" />
                    <CommandList>
                        <CommandEmpty>No models found.</CommandEmpty>
                        {Array.from(grouped.entries()).map(([provider, items]) => (
                            <CommandGroup key={provider} heading={provider}>
                                {items.map((model) => (
                                    <CommandItem
                                        key={model.id}
                                        value={model.id}
                                        onSelect={(v) => {
                                            onChange?.(v);
                                            setOpen(false);
                                        }}
                                        className="flex items-center justify-between"
                                    >
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium truncate">{model.label}</p>
                                            {model.contextWindow && (
                                                <p className="text-[10px] text-muted-foreground">
                                                    {(model.contextWindow / 1000).toFixed(0)}K context
                                                </p>
                                            )}
                                        </div>
                                        {value === model.id && (
                                            <CheckIcon className="size-3.5 shrink-0 text-primary" />
                                        )}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        ))}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
