import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const progressCircleVariants = cva("", {
  variants: {
    size: {
      xs: "size-8",
      sm: "size-12",
      md: "size-16",
      lg: "size-24",
      xl: "size-32",
    },
    color: {
      default: "text-primary",
      success: "text-success",
      warning: "text-warning",
      error: "text-error",
      info: "text-info",
    },
  },
  defaultVariants: {
    size: "md",
    color: "default",
  },
})

interface ProgressCircleProps
  extends Omit<React.SVGAttributes<SVGSVGElement>, "color">,
    VariantProps<typeof progressCircleVariants> {
  value?: number
  max?: number
  showValue?: boolean
  strokeWidth?: number
  label?: string
}

function ProgressCircle({
  value = 0,
  max = 100,
  showValue = false,
  strokeWidth = 4,
  size,
  color,
  className,
  label,
  ...props
}: ProgressCircleProps) {
  const percentage = Math.min(100, Math.max(0, (value / max) * 100))
  const radius = 50 - strokeWidth / 2
  const circumference = 2 * Math.PI * radius
  const strokeDashoffset = circumference - (percentage / 100) * circumference

  return (
    <div
      data-slot="progress-circle"
      role="progressbar"
      aria-valuenow={value}
      aria-valuemin={0}
      aria-valuemax={max}
      aria-label={label ?? `${Math.round(percentage)}%`}
      className={cn("relative inline-flex items-center justify-center", progressCircleVariants({ size }), className)}
    >
      <svg
        className="size-full -rotate-90"
        viewBox="0 0 100 100"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        {...props}
      >
        <circle
          cx="50"
          cy="50"
          r={radius}
          strokeWidth={strokeWidth}
          className="stroke-current opacity-15"
        />
        <circle
          cx="50"
          cy="50"
          r={radius}
          strokeWidth={strokeWidth}
          strokeDasharray={circumference}
          strokeDashoffset={strokeDashoffset}
          strokeLinecap="round"
          className={cn("stroke-current transition-all duration-300", progressCircleVariants({ color }))}
          style={{ strokeDashoffset }}
        />
      </svg>
      {showValue && (
        <span className={cn("absolute text-xs font-medium", progressCircleVariants({ color }))}>
          {Math.round(percentage)}%
        </span>
      )}
    </div>
  )
}

export { ProgressCircle }
export type { ProgressCircleProps }
