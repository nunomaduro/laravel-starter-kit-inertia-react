import * as React from "react"

import { cn } from "@/lib/utils"
import { Checkbox } from "@/components/ui/checkbox"
import { Label } from "@/components/ui/label"

export interface CheckboxGroupOption {
  value: string
  label: string
  description?: string
  disabled?: boolean
}

interface CheckboxGroupProps {
  options: CheckboxGroupOption[]
  value?: string[]
  defaultValue?: string[]
  onChange?: (value: string[]) => void
  orientation?: "horizontal" | "vertical"
  className?: string
  disabled?: boolean
}

function CheckboxGroup({
  options,
  value,
  defaultValue = [],
  onChange,
  orientation = "vertical",
  className,
  disabled = false,
}: CheckboxGroupProps) {
  const [internalValue, setInternalValue] = React.useState<string[]>(defaultValue)
  const isControlled = value !== undefined
  const currentValue = isControlled ? value : internalValue

  const handleCheckedChange = (optionValue: string, checked: boolean) => {
    const next = checked
      ? [...currentValue, optionValue]
      : currentValue.filter((v) => v !== optionValue)

    if (!isControlled) {
      setInternalValue(next)
    }
    onChange?.(next)
  }

  return (
    <div
      data-slot="checkbox-group"
      className={cn(
        orientation === "horizontal" ? "flex flex-row flex-wrap gap-4" : "grid gap-2",
        className
      )}
    >
      {options.map((option) => {
        const id = `checkbox-group-${option.value}`
        const isChecked = currentValue.includes(option.value)
        const isDisabled = disabled || option.disabled

        return (
          <div key={option.value} className="flex items-start gap-2">
            <Checkbox
              id={id}
              checked={isChecked}
              disabled={isDisabled}
              onCheckedChange={(checked) => handleCheckedChange(option.value, !!checked)}
              className="mt-0.5"
            />
            <div>
              <Label htmlFor={id} className="cursor-pointer font-normal">
                {option.label}
              </Label>
              {option.description && (
                <p className="text-muted-foreground text-xs">{option.description}</p>
              )}
            </div>
          </div>
        )
      })}
    </div>
  )
}

export { CheckboxGroup }
