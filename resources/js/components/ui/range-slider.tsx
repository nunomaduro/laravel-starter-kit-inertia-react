import * as React from "react"
import { Slider as SliderPrimitive } from "radix-ui"

import { cn } from "@/lib/utils"

interface RangeSliderProps extends Omit<React.ComponentProps<typeof SliderPrimitive.Root>, "value" | "defaultValue" | "onValueChange"> {
  value?: [number, number]
  defaultValue?: [number, number]
  onValueChange?: (value: [number, number]) => void
  showValues?: boolean
  formatValue?: (value: number) => string
  className?: string
}

function RangeSlider({
  value,
  defaultValue = [0, 100],
  onValueChange,
  min = 0,
  max = 100,
  step = 1,
  showValues = false,
  formatValue = (v) => String(v),
  className,
  ...props
}: RangeSliderProps) {
  const [internalValue, setInternalValue] = React.useState<[number, number]>(defaultValue)
  const isControlled = value !== undefined
  const current = isControlled ? value : internalValue

  const handleValueChange = (vals: number[]) => {
    const next: [number, number] = [vals[0] ?? 0, vals[1] ?? 100]
    if (!isControlled) setInternalValue(next)
    onValueChange?.(next)
  }

  return (
    <div data-slot="range-slider" className={cn("space-y-2", className)}>
      {showValues && (
        <div className="flex justify-between text-xs text-muted-foreground">
          <span>{formatValue(current[0])}</span>
          <span>{formatValue(current[1])}</span>
        </div>
      )}
      <SliderPrimitive.Root
        value={current}
        defaultValue={defaultValue}
        min={min}
        max={max}
        step={step}
        onValueChange={handleValueChange}
        className={cn(
          "relative flex w-full touch-none items-center select-none data-[disabled]:opacity-50"
        )}
        {...props}
      >
        <SliderPrimitive.Track className="bg-muted relative h-1.5 w-full grow overflow-hidden rounded-full">
          <SliderPrimitive.Range className="bg-primary absolute h-full" />
        </SliderPrimitive.Track>
        {current.map((_, index) => (
          <SliderPrimitive.Thumb
            key={index}
            className="border-primary ring-ring/50 block size-4 shrink-0 rounded-full border bg-white shadow-sm transition-[color,box-shadow] hover:ring-4 focus-visible:ring-4 focus-visible:outline-hidden disabled:pointer-events-none disabled:opacity-50"
          />
        ))}
      </SliderPrimitive.Root>
    </div>
  )
}

export { RangeSlider }
