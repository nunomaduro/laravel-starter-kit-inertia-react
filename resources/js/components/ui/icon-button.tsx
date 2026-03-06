import * as React from "react"
import { type VariantProps } from "class-variance-authority"
import { Slot } from "radix-ui"

import { cn } from "@/lib/utils"
import { buttonVariants } from "@/components/ui/button"

interface IconButtonProps
  extends Omit<React.ComponentProps<"button">, "color">,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean
  /** Accessible label (used as aria-label when no visible text). */
  label: string
  color?: "primary" | "secondary" | "info" | "success" | "warning" | "error" | "neutral"
}

function IconButton({
  className,
  variant = "ghost",
  size = "icon",
  color,
  asChild = false,
  label,
  children,
  ...props
}: IconButtonProps) {
  const Comp = asChild ? Slot.Root : "button"

  return (
    <Comp
      data-slot="icon-button"
      aria-label={label}
      title={label}
      className={cn(buttonVariants({ variant, size, color }), className)}
      {...props}
    >
      {children}
    </Comp>
  )
}

export { IconButton }
export type { IconButtonProps }
