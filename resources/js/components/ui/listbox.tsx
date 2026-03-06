import * as React from "react"
import { CheckIcon } from "lucide-react"

import { cn } from "@/lib/utils"

export interface ListboxOption {
  value: string
  label: string
  description?: string
  disabled?: boolean
  icon?: React.ReactNode
}

interface ListboxProps {
  options: ListboxOption[]
  value?: string | string[]
  defaultValue?: string | string[]
  onChange?: (value: string | string[]) => void
  multiple?: boolean
  className?: string
  disabled?: boolean
  size?: "sm" | "md" | "lg"
}

const sizeClasses = {
  sm: "max-h-40",
  md: "max-h-60",
  lg: "max-h-80",
}

function Listbox({
  options,
  value,
  defaultValue,
  onChange,
  multiple = false,
  className,
  disabled = false,
  size = "md",
}: ListboxProps) {
  const defaultVal = defaultValue ?? (multiple ? [] : "")
  const [internalValue, setInternalValue] = React.useState<string | string[]>(defaultVal)
  const isControlled = value !== undefined
  const current = isControlled ? value : internalValue

  const isSelected = (optValue: string) =>
    Array.isArray(current) ? current.includes(optValue) : current === optValue

  const handleSelect = (optValue: string) => {
    if (disabled) return
    let next: string | string[]
    if (multiple) {
      const arr = Array.isArray(current) ? current : []
      next = isSelected(optValue) ? arr.filter((v) => v !== optValue) : [...arr, optValue]
    } else {
      next = current === optValue ? "" : optValue
    }
    if (!isControlled) setInternalValue(next)
    onChange?.(next)
  }

  return (
    <div
      data-slot="listbox"
      role="listbox"
      aria-multiselectable={multiple}
      className={cn(
        "border-input bg-background overflow-y-auto rounded-md border shadow-xs",
        sizeClasses[size],
        disabled && "cursor-not-allowed opacity-50",
        className
      )}
    >
      {options.map((option) => {
        const selected = isSelected(option.value)
        return (
          <div
            key={option.value}
            role="option"
            aria-selected={selected}
            aria-disabled={option.disabled ?? disabled}
            onClick={() => !option.disabled && handleSelect(option.value)}
            className={cn(
              "flex cursor-pointer items-center gap-2 px-3 py-2 text-sm transition-colors",
              "hover:bg-accent hover:text-accent-foreground",
              selected && "bg-primary/10 text-primary",
              option.disabled && "cursor-not-allowed opacity-50 hover:bg-transparent hover:text-foreground"
            )}
          >
            {option.icon && <span className="shrink-0">{option.icon}</span>}
            <div className="flex-1">
              <p>{option.label}</p>
              {option.description && (
                <p className="text-muted-foreground text-xs">{option.description}</p>
              )}
            </div>
            {selected && <CheckIcon className="size-4 shrink-0" />}
          </div>
        )
      })}
    </div>
  )
}

export { Listbox }
