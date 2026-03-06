import * as React from "react"
import { CheckIcon, ChevronDownIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Badge } from "@/components/ui/badge"
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

export interface MultiSelectOption {
  value: string
  label: string
  icon?: React.ReactNode
  disabled?: boolean
}

interface MultiSelectProps {
  options: MultiSelectOption[]
  value?: string[]
  onValueChange?: (value: string[]) => void
  placeholder?: string
  maxCount?: number
  className?: string
  disabled?: boolean
}

function MultiSelect({
  options,
  value = [],
  onValueChange,
  placeholder = "Select options...",
  maxCount = 3,
  className,
  disabled,
}: MultiSelectProps) {
  const [open, setOpen] = React.useState(false)

  const toggleOption = (optionValue: string) => {
    const newValue = value.includes(optionValue)
      ? value.filter((v) => v !== optionValue)
      : [...value, optionValue]
    onValueChange?.(newValue)
  }

  const clearAll = (e: React.MouseEvent) => {
    e.stopPropagation()
    onValueChange?.([])
  }

  const removeOption = (e: React.MouseEvent, optionValue: string) => {
    e.stopPropagation()
    onValueChange?.(value.filter((v) => v !== optionValue))
  }

  const selectedOptions = options.filter((o) => value.includes(o.value))
  const displayedOptions = selectedOptions.slice(0, maxCount)
  const remaining = selectedOptions.length - maxCount

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          disabled={disabled}
          className={cn(
            "h-auto min-h-9 w-full justify-between px-3 font-normal",
            className
          )}
        >
          <div className="flex flex-1 flex-wrap gap-1">
            {selectedOptions.length === 0 && (
              <span className="text-muted-foreground">{placeholder}</span>
            )}
            {displayedOptions.map((option) => (
              <Badge
                key={option.value}
                variant="secondary"
                className="gap-1 pr-1"
              >
                {option.label}
                <span
                  role="button"
                  onClick={(e) => removeOption(e, option.value)}
                  className="cursor-pointer rounded-sm opacity-60 hover:opacity-100"
                >
                  <XIcon className="size-3" />
                </span>
              </Badge>
            ))}
            {remaining > 0 && (
              <Badge variant="secondary">+{remaining}</Badge>
            )}
          </div>
          <div className="flex shrink-0 items-center gap-1 self-start">
            {selectedOptions.length > 0 && (
              <span
                role="button"
                onClick={clearAll}
                className="cursor-pointer opacity-60 hover:opacity-100"
              >
                <XIcon className="size-4" />
              </span>
            )}
            <ChevronDownIcon className="size-4 opacity-50" />
          </div>
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-full p-0" align="start">
        <Command>
          <CommandInput placeholder="Search options..." />
          <CommandList>
            <CommandEmpty>No options found.</CommandEmpty>
            <CommandGroup>
              {options.map((option) => (
                <CommandItem
                  key={option.value}
                  value={option.value}
                  onSelect={() => toggleOption(option.value)}
                  disabled={option.disabled}
                >
                  <CheckIcon
                    className={cn(
                      "mr-2 size-4",
                      value.includes(option.value) ? "opacity-100" : "opacity-0"
                    )}
                  />
                  {option.icon && (
                    <span className="mr-2">{option.icon}</span>
                  )}
                  {option.label}
                </CommandItem>
              ))}
            </CommandGroup>
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  )
}

export { MultiSelect }
