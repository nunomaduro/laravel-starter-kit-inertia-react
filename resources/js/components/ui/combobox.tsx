import * as React from "react"
import { CheckIcon, ChevronsUpDownIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/components/ui/command"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

export interface ComboboxOption {
  value: string
  label: string
  description?: string
  disabled?: boolean
}

export interface ComboboxGroup {
  label: string
  options: ComboboxOption[]
}

interface ComboboxProps {
  options?: ComboboxOption[]
  groups?: ComboboxGroup[]
  value?: string
  defaultValue?: string
  onChange?: (value: string) => void
  placeholder?: string
  searchPlaceholder?: string
  emptyText?: string
  className?: string
  disabled?: boolean
  multiple?: false
}

interface MultiComboboxProps extends Omit<ComboboxProps, "multiple" | "value" | "onChange"> {
  multiple: true
  value?: string[]
  onChange?: (value: string[]) => void
}

function Combobox({
  options = [],
  groups,
  value,
  defaultValue,
  onChange,
  placeholder = "Select option...",
  searchPlaceholder = "Search...",
  emptyText = "No results found.",
  className,
  disabled = false,
}: ComboboxProps | MultiComboboxProps) {
  const [open, setOpen] = React.useState(false)
  const [internalValue, setInternalValue] = React.useState<string | string[]>(defaultValue ?? "")

  const isControlled = value !== undefined
  const current = isControlled ? value : internalValue

  const allOptions = groups ? groups.flatMap((g) => g.options) : options

  const isSelected = (optValue: string) =>
    Array.isArray(current) ? current.includes(optValue) : current === optValue

  const getLabel = () => {
    if (Array.isArray(current)) {
      if (current.length === 0) return null
      if (current.length === 1) {
        return allOptions.find((o) => o.value === current[0])?.label
      }
      return `${current.length} selected`
    }
    return allOptions.find((o) => o.value === current)?.label ?? null
  }

  const handleSelect = (optValue: string) => {
    if (Array.isArray(current)) {
      const next = isSelected(optValue)
        ? current.filter((v) => v !== optValue)
        : [...current, optValue]
      if (!isControlled) setInternalValue(next)
      ;(onChange as ((v: string[]) => void) | undefined)?.(next)
    } else {
      const next = current === optValue ? "" : optValue
      if (!isControlled) setInternalValue(next)
      ;(onChange as ((v: string) => void) | undefined)?.(next)
      setOpen(false)
    }
  }

  const renderOptions = (opts: ComboboxOption[]) =>
    opts.map((opt) => (
      <CommandItem
        key={opt.value}
        value={opt.value}
        disabled={opt.disabled}
        onSelect={() => handleSelect(opt.value)}
        className="gap-2"
      >
        <CheckIcon
          className={cn("size-4 shrink-0", isSelected(opt.value) ? "opacity-100" : "opacity-0")}
        />
        <div>
          <p className="text-sm">{opt.label}</p>
          {opt.description && (
            <p className="text-muted-foreground text-xs">{opt.description}</p>
          )}
        </div>
      </CommandItem>
    ))

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          disabled={disabled}
          className={cn("w-full justify-between font-normal", !getLabel() && "text-muted-foreground", className)}
        >
          <span className="truncate">{getLabel() ?? placeholder}</span>
          <ChevronsUpDownIcon className="ml-2 size-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-[--radix-popover-trigger-width] p-0" align="start">
        <Command>
          <CommandInput placeholder={searchPlaceholder} />
          <CommandList>
            <CommandEmpty>{emptyText}</CommandEmpty>
            {groups
              ? groups.map((group) => (
                  <CommandGroup key={group.label} heading={group.label}>
                    {renderOptions(group.options)}
                  </CommandGroup>
                ))
              : <CommandGroup>{renderOptions(options)}</CommandGroup>}
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  )
}

export { Combobox }
