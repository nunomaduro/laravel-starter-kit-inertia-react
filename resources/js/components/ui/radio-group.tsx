import * as React from "react"
import { RadioGroup as RadioGroupPrimitive } from "radix-ui"

import { cn } from "@/lib/utils"
import { Label } from "@/components/ui/label"

function RadioGroup({
  className,
  ...props
}: React.ComponentProps<typeof RadioGroupPrimitive.Root>) {
  return (
    <RadioGroupPrimitive.Root
      data-slot="radio-group"
      className={cn("grid gap-2", className)}
      {...props}
    />
  )
}

function RadioGroupItem({
  className,
  ...props
}: React.ComponentProps<typeof RadioGroupPrimitive.Item>) {
  return (
    <RadioGroupPrimitive.Item
      data-slot="radio-group-item"
      className={cn(
        "border-input text-primary focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 aspect-square size-4 shrink-0 rounded-full border shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50",
        className
      )}
      {...props}
    >
      <RadioGroupPrimitive.Indicator
        data-slot="radio-group-indicator"
        className="relative flex items-center justify-center after:block after:size-2 after:rounded-full after:bg-current"
      />
    </RadioGroupPrimitive.Item>
  )
}

export interface RadioGroupOption {
  value: string
  label: string
  description?: string
  disabled?: boolean
}

interface RadioGroupWithOptionsProps extends Omit<React.ComponentProps<typeof RadioGroupPrimitive.Root>, "children"> {
  options: RadioGroupOption[]
  orientation?: "horizontal" | "vertical"
}

function RadioGroupWithOptions({
  options,
  orientation = "vertical",
  className,
  ...props
}: RadioGroupWithOptionsProps) {
  return (
    <RadioGroup
      className={cn(
        orientation === "horizontal" ? "flex flex-row flex-wrap gap-4" : "grid gap-2",
        className
      )}
      {...props}
    >
      {options.map((option) => (
        <div key={option.value} className="flex items-start gap-2">
          <RadioGroupItem
            id={`radio-${option.value}`}
            value={option.value}
            disabled={option.disabled}
            className="mt-0.5"
          />
          <div>
            <Label htmlFor={`radio-${option.value}`} className="cursor-pointer font-normal">
              {option.label}
            </Label>
            {option.description && (
              <p className="text-muted-foreground text-xs">{option.description}</p>
            )}
          </div>
        </div>
      ))}
    </RadioGroup>
  )
}

export { RadioGroup, RadioGroupItem, RadioGroupWithOptions }
