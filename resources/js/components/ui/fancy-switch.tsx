import * as React from "react"
import { Switch as SwitchPrimitive } from "radix-ui"

import { cn } from "@/lib/utils"

interface FancySwitchProps extends Omit<React.ComponentProps<typeof SwitchPrimitive.Root>, "children"> {
  label?: string
  description?: string
  icon?: React.ReactNode
  checkedIcon?: React.ReactNode
  uncheckedIcon?: React.ReactNode
  size?: "sm" | "md" | "lg"
  color?: "default" | "success" | "warning" | "error" | "info"
}

const colorClass: Record<string, string> = {
  default: "data-[state=checked]:bg-primary",
  success: "data-[state=checked]:bg-green-500",
  warning: "data-[state=checked]:bg-amber-500",
  error: "data-[state=checked]:bg-red-500",
  info: "data-[state=checked]:bg-blue-500",
}

const sizeClass = {
  sm: { root: "h-5 w-9", thumb: "size-4 data-[state=checked]:translate-x-4" },
  md: { root: "h-6 w-11", thumb: "size-5 data-[state=checked]:translate-x-5" },
  lg: { root: "h-7 w-14", thumb: "size-6 data-[state=checked]:translate-x-7" },
}

function FancySwitch({
  label,
  description,
  icon,
  checkedIcon,
  uncheckedIcon,
  size = "md",
  color = "default",
  className,
  ...props
}: FancySwitchProps) {
  const { root, thumb } = sizeClass[size]

  const switchEl = (
    <SwitchPrimitive.Root
      data-slot="fancy-switch"
      className={cn(
        "peer relative inline-flex shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent bg-input shadow-inner transition-colors duration-300",
        "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2",
        "disabled:cursor-not-allowed disabled:opacity-50",
        colorClass[color],
        root,
        className
      )}
      {...props}
    >
      <SwitchPrimitive.Thumb
        data-slot="fancy-switch-thumb"
        className={cn(
          "pointer-events-none flex items-center justify-center rounded-full bg-background shadow-md transition-transform duration-300",
          thumb
        )}
      >
        {checkedIcon || uncheckedIcon ? (
          <span className="text-muted-foreground [*[data-state=checked]_&]:text-primary transition-opacity">
            {props.checked ? checkedIcon : uncheckedIcon}
          </span>
        ) : null}
        {icon && (
          <span className="text-muted-foreground">{icon}</span>
        )}
      </SwitchPrimitive.Thumb>
    </SwitchPrimitive.Root>
  )

  if (label || description) {
    return (
      <label className="flex cursor-pointer items-center gap-3">
        {switchEl}
        <div>
          {label && <p className="text-sm font-medium">{label}</p>}
          {description && (
            <p className="text-xs text-muted-foreground">{description}</p>
          )}
        </div>
      </label>
    )
  }

  return switchEl
}

export { FancySwitch }
