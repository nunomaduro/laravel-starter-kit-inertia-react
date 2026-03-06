import * as React from "react"
import { StarIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface RatingProps {
  value?: number
  defaultValue?: number
  onChange?: (value: number) => void
  max?: number
  precision?: 0.5 | 1
  size?: "sm" | "md" | "lg"
  disabled?: boolean
  readOnly?: boolean
  className?: string
  icon?: React.ReactNode
  emptyIcon?: React.ReactNode
}

const sizeClasses = {
  sm: "size-4",
  md: "size-5",
  lg: "size-7",
}

function Rating({
  value,
  defaultValue = 0,
  onChange,
  max = 5,
  precision = 1,
  size = "md",
  disabled = false,
  readOnly = false,
  className,
  icon,
  emptyIcon,
}: RatingProps) {
  const isControlled = value !== undefined
  const [internalValue, setInternalValue] = React.useState(defaultValue)
  const [hoverValue, setHoverValue] = React.useState<number | null>(null)
  const current = isControlled ? value : internalValue
  const displayValue = hoverValue ?? current

  const getStarValue = (starIndex: number, isHalf: boolean) =>
    precision === 0.5 ? starIndex + (isHalf ? 0 : 0.5) : starIndex

  const handleClick = (starValue: number) => {
    if (disabled || readOnly) return
    const next = current === starValue ? 0 : starValue
    if (!isControlled) setInternalValue(next)
    onChange?.(next)
  }

  const handleMouseMove = (e: React.MouseEvent<HTMLButtonElement>, starIndex: number) => {
    if (precision === 0.5) {
      const rect = e.currentTarget.getBoundingClientRect()
      const half = e.clientX < rect.left + rect.width / 2
      setHoverValue(starIndex + (half ? 0.5 : 1))
    } else {
      setHoverValue(starIndex + 1)
    }
  }

  const iconSize = sizeClasses[size]

  return (
    <div
      data-slot="rating"
      className={cn("inline-flex items-center gap-0.5", className)}
      onMouseLeave={() => setHoverValue(null)}
    >
      {Array.from({ length: max }, (_, i) => {
        const starFull = displayValue >= i + 1
        const starHalf = !starFull && displayValue >= i + 0.5

        return (
          <button
            key={i}
            type="button"
            disabled={disabled || readOnly}
            onClick={() => handleClick(precision === 0.5 ? (hoverValue ?? i + 1) : i + 1)}
            onMouseMove={(e) => handleMouseMove(e, i)}
            className={cn(
              "relative transition-transform focus-visible:outline-none",
              !disabled && !readOnly && "hover:scale-110 cursor-pointer",
              disabled && "cursor-not-allowed opacity-50"
            )}
            aria-label={`Rate ${i + 1} of ${max}`}
          >
            {starHalf ? (
              <span className="relative inline-block">
                <StarIcon className={cn(iconSize, "text-muted-foreground")} />
                <span className="absolute inset-0 overflow-hidden w-1/2">
                  <StarIcon className={cn(iconSize, "text-amber-400 fill-amber-400")} />
                </span>
              </span>
            ) : starFull ? (
              (icon ?? <StarIcon className={cn(iconSize, "text-amber-400 fill-amber-400")} />)
            ) : (
              (emptyIcon ?? <StarIcon className={cn(iconSize, "text-muted-foreground")} />)
            )}
          </button>
        )
      })}
    </div>
  )
}

export { Rating }
