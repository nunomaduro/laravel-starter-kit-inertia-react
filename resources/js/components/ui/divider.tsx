import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const dividerVariants = cva("shrink-0 bg-border", {
  variants: {
    orientation: {
      horizontal: "h-px w-full",
      vertical: "h-full w-px",
    },
    variant: {
      solid: "",
      dashed: "border-dashed bg-transparent border-b border-border",
      dotted: "border-dotted bg-transparent border-b border-border",
    },
  },
  defaultVariants: {
    orientation: "horizontal",
    variant: "solid",
  },
})

interface DividerProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof dividerVariants> {
  label?: React.ReactNode
  labelPosition?: "start" | "center" | "end"
}

function Divider({
  className,
  orientation = "horizontal",
  variant = "solid",
  label,
  labelPosition = "center",
  ...props
}: DividerProps) {
  if (label && orientation !== "vertical") {
    const alignClass =
      labelPosition === "start"
        ? "justify-start"
        : labelPosition === "end"
          ? "justify-end"
          : "justify-center"

    return (
      <div
        data-slot="divider"
        role="separator"
        className={cn("flex items-center gap-3", alignClass, className)}
        {...props}
      >
        <span
          className={cn(
            dividerVariants({ orientation, variant }),
            labelPosition !== "start" && "flex-1"
          )}
        />
        <span className="shrink-0 text-xs text-muted-foreground">{label}</span>
        <span
          className={cn(
            dividerVariants({ orientation, variant }),
            labelPosition !== "end" && "flex-1"
          )}
        />
      </div>
    )
  }

  return (
    <div
      data-slot="divider"
      role="separator"
      className={cn(dividerVariants({ orientation, variant }), className)}
      {...props}
    />
  )
}

export { Divider, dividerVariants }
export type { DividerProps }
