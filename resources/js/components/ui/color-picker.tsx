import * as React from "react"
import { HexColorInput, HexColorPicker } from "react-colorful"

import { cn } from "@/lib/utils"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface ColorPickerProps {
  value?: string
  onChange?: (color: string) => void
  className?: string
  disabled?: boolean
  showInput?: boolean
  presetColors?: string[]
}

function ColorPicker({
  value = "#000000",
  onChange,
  className,
  disabled,
  showInput = true,
  presetColors,
}: ColorPickerProps) {
  return (
    <Popover>
      <PopoverTrigger asChild>
        <button
          type="button"
          disabled={disabled}
          className={cn(
            "flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-input shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50",
            className
          )}
          style={{ backgroundColor: value }}
          aria-label="Pick a color"
        />
      </PopoverTrigger>
      <PopoverContent className="w-auto p-3" align="start">
        <div data-slot="color-picker" className="flex flex-col gap-3">
          <HexColorPicker color={value} onChange={onChange} />
          {presetColors && presetColors.length > 0 && (
            <div className="flex flex-wrap gap-1.5">
              {presetColors.map((color) => (
                <button
                  key={color}
                  type="button"
                  className="size-6 rounded-md border border-border shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                  style={{ backgroundColor: color }}
                  onClick={() => onChange?.(color)}
                  aria-label={color}
                />
              ))}
            </div>
          )}
          {showInput && (
            <div className="flex items-center gap-2">
              <span className="text-sm text-muted-foreground">#</span>
              <HexColorInput
                color={value}
                onChange={onChange}
                prefixed={false}
                className="h-8 w-full rounded-md border border-input bg-background px-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring uppercase"
              />
            </div>
          )}
        </div>
      </PopoverContent>
    </Popover>
  )
}

interface ColorSwatchProps {
  color: string
  size?: "sm" | "md" | "lg"
  className?: string
  onClick?: () => void
}

function ColorSwatch({ color, size = "md", className, onClick }: ColorSwatchProps) {
  const sizeClass = {
    sm: "size-4",
    md: "size-6",
    lg: "size-8",
  }[size]

  return (
    <button
      type="button"
      className={cn(
        "rounded-md border border-border shadow-xs",
        sizeClass,
        onClick && "cursor-pointer",
        className
      )}
      style={{ backgroundColor: color }}
      onClick={onClick}
      aria-label={color}
    />
  )
}

export { ColorPicker, ColorSwatch }
