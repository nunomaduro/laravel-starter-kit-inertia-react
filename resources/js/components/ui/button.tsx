import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { Loader2Icon } from "lucide-react"
import { Slot } from "radix-ui"

import { cn } from "@/lib/utils"

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground hover:bg-primary/90",
        destructive:
          "bg-destructive text-white hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60",
        outline:
          "border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50",
        secondary:
          "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        ghost:
          "hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50",
        link: "text-primary underline-offset-4 hover:underline",
        filled: "bg-primary text-primary-foreground hover:bg-primary/90",
        soft: "bg-primary/10 text-primary hover:bg-primary/20 dark:bg-primary/20 dark:hover:bg-primary/30",
        flat: "text-primary hover:bg-primary/10",
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
      size: {
        default: "h-9 px-4 py-2 has-[>svg]:px-3",
        xs: "h-6 gap-1 rounded-md px-2 text-xs has-[>svg]:px-1.5 [&_svg:not([class*='size-'])]:size-3",
        sm: "h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5",
        lg: "h-10 rounded-md px-6 has-[>svg]:px-4",
        icon: "size-9",
        "icon-xs": "size-6 rounded-md [&_svg:not([class*='size-'])]:size-3",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    compoundVariants: [
      { variant: "filled", color: "primary", class: "bg-primary text-primary-foreground hover:bg-primary/90" },
      { variant: "filled", color: "secondary", class: "bg-secondary text-white hover:bg-secondary/90 dark:text-black" },
      { variant: "filled", color: "info", class: "bg-info text-white hover:bg-info/90" },
      { variant: "filled", color: "success", class: "bg-success text-white hover:bg-success/90" },
      { variant: "filled", color: "warning", class: "bg-warning text-black hover:bg-warning/90" },
      { variant: "filled", color: "error", class: "bg-error text-white hover:bg-error/90" },
      { variant: "filled", color: "neutral", class: "bg-neutral-700 text-white hover:bg-neutral-600 dark:bg-neutral-300 dark:text-neutral-900" },
      { variant: "soft", color: "primary", class: "bg-primary/10 text-primary hover:bg-primary/20 dark:bg-primary/20 dark:hover:bg-primary/30" },
      { variant: "soft", color: "secondary", class: "bg-secondary/10 text-secondary hover:bg-secondary/20 dark:bg-secondary/20" },
      { variant: "soft", color: "info", class: "bg-info/10 text-info hover:bg-info/20 dark:bg-info/20" },
      { variant: "soft", color: "success", class: "bg-success/10 text-success hover:bg-success/20 dark:bg-success/20" },
      { variant: "soft", color: "warning", class: "bg-warning/10 text-warning hover:bg-warning/20 dark:bg-warning/20" },
      { variant: "soft", color: "error", class: "bg-error/10 text-error hover:bg-error/20 dark:bg-error/20" },
      { variant: "soft", color: "neutral", class: "bg-muted text-muted-foreground hover:bg-muted/80" },
      { variant: "flat", color: "primary", class: "text-primary hover:bg-primary/10" },
      { variant: "flat", color: "secondary", class: "text-secondary hover:bg-secondary/10" },
      { variant: "flat", color: "info", class: "text-info hover:bg-info/10" },
      { variant: "flat", color: "success", class: "text-success hover:bg-success/10" },
      { variant: "flat", color: "warning", class: "text-warning hover:bg-warning/10" },
      { variant: "flat", color: "error", class: "text-error hover:bg-error/10" },
      { variant: "flat", color: "neutral", class: "text-muted-foreground hover:bg-muted" },
    ],
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

type SemanticColor = "primary" | "secondary" | "info" | "success" | "warning" | "error" | "neutral"

function Button({
  className,
  variant = "default",
  size = "default",
  color,
  isLoading = false,
  leftIcon,
  rightIcon,
  asChild = false,
  children,
  disabled,
  ...props
}: Omit<React.ComponentProps<"button">, "color"> &
  VariantProps<typeof buttonVariants> & {
    asChild?: boolean
    color?: SemanticColor
    isLoading?: boolean
    leftIcon?: React.ReactNode
    rightIcon?: React.ReactNode
  }) {
  const Comp = asChild ? Slot.Root : "button"

  return (
    <Comp
      data-slot="button"
      data-variant={variant}
      data-size={size}
      className={cn(buttonVariants({ variant, size, color, className }))}
      disabled={disabled || isLoading}
      {...props}
    >
      {isLoading ? (
        <Loader2Icon className="animate-spin" aria-hidden />
      ) : (
        (leftIcon ?? null)
      )}
      {children}
      {!isLoading && (rightIcon ?? null)}
    </Comp>
  )
}

export { Button, buttonVariants }
