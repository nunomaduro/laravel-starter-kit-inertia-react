import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const alertVariants = cva(
  "relative w-full rounded-lg border px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current",
  {
    variants: {
      variant: {
        default: "bg-background text-foreground",
        destructive:
          "text-destructive-foreground [&>svg]:text-current *:data-[slot=alert-description]:text-destructive-foreground/80",
        filled: "border-transparent bg-primary text-primary-foreground",
        soft: "border-primary/20 bg-primary/10 text-primary dark:bg-primary/20",
        outlined: "bg-transparent text-foreground",
      },
      color: {
        primary: "",
        secondary: "",
        info: "",
        success: "",
        warning: "",
        error: "",
        neutral: "",
      },
    },
    compoundVariants: [
      { variant: "filled", color: "primary", class: "bg-primary text-primary-foreground border-transparent" },
      { variant: "filled", color: "secondary", class: "bg-secondary text-white border-transparent dark:text-black" },
      { variant: "filled", color: "info", class: "bg-info text-white border-transparent" },
      { variant: "filled", color: "success", class: "bg-success text-white border-transparent" },
      { variant: "filled", color: "warning", class: "bg-warning text-black border-transparent" },
      { variant: "filled", color: "error", class: "bg-error text-white border-transparent" },
      { variant: "filled", color: "neutral", class: "bg-neutral-700 text-white border-transparent dark:bg-neutral-300 dark:text-neutral-900" },
      { variant: "soft", color: "primary", class: "bg-primary/10 text-primary border-primary/20 dark:bg-primary/20" },
      { variant: "soft", color: "secondary", class: "bg-secondary/10 text-secondary border-secondary/20 dark:bg-secondary/20" },
      { variant: "soft", color: "info", class: "bg-info/10 text-info border-info/20 dark:bg-info/20" },
      { variant: "soft", color: "success", class: "bg-success/10 text-success border-success/20 dark:bg-success/20" },
      { variant: "soft", color: "warning", class: "bg-warning/10 text-warning border-warning/20 dark:bg-warning/20" },
      { variant: "soft", color: "error", class: "bg-error/10 text-error border-error/20 dark:bg-error/20" },
      { variant: "soft", color: "neutral", class: "bg-muted text-muted-foreground border-border" },
      { variant: "outlined", color: "primary", class: "border-primary text-primary" },
      { variant: "outlined", color: "secondary", class: "border-secondary text-secondary" },
      { variant: "outlined", color: "info", class: "border-info text-info" },
      { variant: "outlined", color: "success", class: "border-success text-success" },
      { variant: "outlined", color: "warning", class: "border-warning text-warning" },
      { variant: "outlined", color: "error", class: "border-error text-error" },
      { variant: "outlined", color: "neutral", class: "border-border text-muted-foreground" },
    ],
    defaultVariants: {
      variant: "default",
    },
  }
)

type SemanticColor = "primary" | "secondary" | "info" | "success" | "warning" | "error" | "neutral"

function Alert({
  className,
  variant,
  color,
  ...props
}: React.ComponentProps<"div"> & VariantProps<typeof alertVariants> & { color?: SemanticColor }) {
  return (
    <div
      data-slot="alert"
      role="alert"
      className={cn(alertVariants({ variant, color }), className)}
      {...props}
    />
  )
}

function AlertTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-title"
      className={cn(
        "col-start-2 line-clamp-1 min-h-4 font-medium tracking-tight",
        className
      )}
      {...props}
    />
  )
}

function AlertDescription({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-description"
      className={cn(
        "text-muted-foreground col-start-2 grid justify-items-start gap-1 text-sm [&_p]:leading-relaxed",
        className
      )}
      {...props}
    />
  )
}

export { Alert, AlertTitle, AlertDescription }
