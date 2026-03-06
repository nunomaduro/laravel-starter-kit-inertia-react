import * as React from "react"
import { MinusIcon, PlusIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface NumberInputProps extends Omit<React.ComponentProps<"input">, "type" | "value" | "onChange"> {
  value?: number
  defaultValue?: number
  onChange?: (value: number) => void
  min?: number
  max?: number
  step?: number
  precision?: number
  allowNegative?: boolean
  prefix?: string
  suffix?: string
  showControls?: boolean
}

function NumberInput({
  value,
  defaultValue = 0,
  onChange,
  min = -Infinity,
  max = Infinity,
  step = 1,
  precision,
  allowNegative = true,
  prefix,
  suffix,
  showControls = true,
  className,
  disabled,
  ...props
}: NumberInputProps) {
  const isControlled = value !== undefined
  const [internalValue, setInternalValue] = React.useState(defaultValue)
  const current = isControlled ? value : internalValue

  const update = (next: number) => {
    const clamped = Math.min(max, Math.max(min, next))
    const rounded = precision !== undefined ? parseFloat(clamped.toFixed(precision)) : clamped
    if (!isControlled) setInternalValue(rounded)
    onChange?.(rounded)
  }

  const increment = () => update(current + step)
  const decrement = () => {
    const next = current - step
    if (!allowNegative && next < 0) return
    update(next)
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value
    const num = parseFloat(raw)
    if (!isNaN(num)) update(num)
  }

  return (
    <div
      data-slot="number-input"
      className={cn(
        "border-input bg-background inline-flex items-center rounded-md border shadow-xs",
        "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
        "has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-50",
        className
      )}
    >
      {showControls && (
        <button
          type="button"
          onClick={decrement}
          disabled={disabled || current <= min || (!allowNegative && current - step < 0)}
          className="flex h-9 w-8 shrink-0 items-center justify-center rounded-l-md border-r text-muted-foreground hover:bg-accent hover:text-foreground disabled:cursor-not-allowed disabled:opacity-50 transition-colors"
          aria-label="Decrease"
        >
          <MinusIcon className="size-3.5" />
        </button>
      )}
      <div className="flex flex-1 items-center">
        {prefix && <span className="pl-3 text-sm text-muted-foreground">{prefix}</span>}
        <input
          type="number"
          value={current}
          onChange={handleChange}
          min={allowNegative ? min : Math.max(0, min)}
          max={max}
          step={step}
          disabled={disabled}
          className="h-9 w-full min-w-0 bg-transparent px-3 text-center text-sm outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none disabled:cursor-not-allowed"
          {...props}
        />
        {suffix && <span className="pr-3 text-sm text-muted-foreground">{suffix}</span>}
      </div>
      {showControls && (
        <button
          type="button"
          onClick={increment}
          disabled={disabled || current >= max}
          className="flex h-9 w-8 shrink-0 items-center justify-center rounded-r-md border-l text-muted-foreground hover:bg-accent hover:text-foreground disabled:cursor-not-allowed disabled:opacity-50 transition-colors"
          aria-label="Increase"
        >
          <PlusIcon className="size-3.5" />
        </button>
      )}
    </div>
  )
}

export { NumberInput }
