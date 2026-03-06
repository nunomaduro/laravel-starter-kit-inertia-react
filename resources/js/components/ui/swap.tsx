import * as React from "react"

import { cn } from "@/lib/utils"

interface SwapProps extends React.HTMLAttributes<HTMLButtonElement> {
  /** Content shown when the swap is in its "on" (active) state. */
  onContent: React.ReactNode
  /** Content shown when the swap is in its "off" (default) state. */
  offContent: React.ReactNode
  /** Controlled state: whether the swap is "on". */
  checked?: boolean
  /** Default state when uncontrolled. */
  defaultChecked?: boolean
  onCheckedChange?: (checked: boolean) => void
  /** Animation style between the two states. */
  animation?: "rotate" | "flip" | "fade"
  disabled?: boolean
}

function Swap({
  className,
  onContent,
  offContent,
  checked,
  defaultChecked = false,
  onCheckedChange,
  animation = "rotate",
  disabled,
  onClick,
  ...props
}: SwapProps) {
  const [internalChecked, setInternalChecked] = React.useState(defaultChecked)
  const isControlled = checked !== undefined
  const isOn = isControlled ? checked : internalChecked

  const handleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
    if (disabled) return
    const next = !isOn
    if (!isControlled) {
      setInternalChecked(next)
    }
    onCheckedChange?.(next)
    onClick?.(e)
  }

  return (
    <button
      type="button"
      data-slot="swap"
      data-checked={isOn || undefined}
      aria-pressed={isOn}
      disabled={disabled}
      onClick={handleClick}
      className={cn(
        "relative inline-flex items-center justify-center overflow-hidden",
        "disabled:pointer-events-none disabled:opacity-50",
        "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2",
        className,
      )}
      {...props}
    >
      {/* "on" slot */}
      <span
        aria-hidden={!isOn}
        className={cn(
          "absolute flex items-center justify-center transition-all duration-200",
          animation === "rotate" && (isOn ? "rotate-0 opacity-100 scale-100" : "rotate-90 opacity-0 scale-50"),
          animation === "flip" && (isOn ? "rotateY-0 opacity-100" : "rotateY-90 opacity-0"),
          animation === "fade" && (isOn ? "opacity-100 scale-100" : "opacity-0 scale-95"),
        )}
      >
        {onContent}
      </span>

      {/* "off" slot */}
      <span
        aria-hidden={isOn}
        className={cn(
          "flex items-center justify-center transition-all duration-200",
          animation === "rotate" && (isOn ? "-rotate-90 opacity-0 scale-50" : "rotate-0 opacity-100 scale-100"),
          animation === "flip" && (isOn ? "rotateY-90 opacity-0" : "rotateY-0 opacity-100"),
          animation === "fade" && (isOn ? "opacity-0 scale-95" : "opacity-100 scale-100"),
        )}
      >
        {offContent}
      </span>
    </button>
  )
}

export { Swap }
export type { SwapProps }
