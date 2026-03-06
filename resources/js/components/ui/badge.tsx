import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const badgeVariants = cva(
  "inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive transition-[color,box-shadow] overflow-hidden",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-primary text-primary-foreground [a&]:hover:bg-primary/90",
        secondary:
          "border-transparent bg-secondary text-secondary-foreground [a&]:hover:bg-secondary/90",
        destructive:
          "border-transparent bg-destructive text-white [a&]:hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60",
        outline:
          "text-foreground [a&]:hover:bg-accent [a&]:hover:text-accent-foreground",
        filled: "border-transparent bg-primary text-primary-foreground",
        soft: "border-transparent bg-primary/10 text-primary dark:bg-primary/20",
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
      { variant: "soft", color: "primary", class: "bg-primary/10 text-primary border-transparent dark:bg-primary/20" },
      { variant: "soft", color: "secondary", class: "bg-secondary/10 text-secondary border-transparent dark:bg-secondary/20" },
      { variant: "soft", color: "info", class: "bg-info/10 text-info border-transparent dark:bg-info/20" },
      { variant: "soft", color: "success", class: "bg-success/10 text-success border-transparent dark:bg-success/20" },
      { variant: "soft", color: "warning", class: "bg-warning/10 text-warning border-transparent dark:bg-warning/20" },
      { variant: "soft", color: "error", class: "bg-error/10 text-error border-transparent dark:bg-error/20" },
      { variant: "soft", color: "neutral", class: "bg-muted text-muted-foreground border-transparent" },
      { variant: "outline", color: "primary", class: "border-primary text-primary bg-transparent" },
      { variant: "outline", color: "secondary", class: "border-secondary text-secondary bg-transparent" },
      { variant: "outline", color: "info", class: "border-info text-info bg-transparent" },
      { variant: "outline", color: "success", class: "border-success text-success bg-transparent" },
      { variant: "outline", color: "warning", class: "border-warning text-warning bg-transparent" },
      { variant: "outline", color: "error", class: "border-error text-error bg-transparent" },
      { variant: "outline", color: "neutral", class: "border-border text-muted-foreground bg-transparent" },
    ],
    defaultVariants: {
      variant: "default",
    },
  }
)

type SemanticColor = "primary" | "secondary" | "info" | "success" | "warning" | "error" | "neutral"

function Badge({
  className,
  variant,
  color,
  glow = false,
  asChild = false,
  ...props
}: React.ComponentProps<"span"> &
  VariantProps<typeof badgeVariants> & {
    asChild?: boolean
    color?: SemanticColor
    glow?: boolean
  }) {
  const Comp = asChild ? Slot : "span"

  const glowClass = glow
    ? color === "info"
      ? "shadow-[0_0_8px_2px_theme(colors.info/0.4)]"
      : color === "success"
        ? "shadow-[0_0_8px_2px_theme(colors.success/0.4)]"
        : color === "warning"
          ? "shadow-[0_0_8px_2px_theme(colors.warning/0.4)]"
          : color === "error"
            ? "shadow-[0_0_8px_2px_theme(colors.error/0.4)]"
            : color === "secondary"
              ? "shadow-[0_0_8px_2px_theme(colors.secondary/0.4)]"
              : color === "neutral"
                ? "shadow-[0_0_8px_2px_rgba(0,0,0,0.2)]"
                : "shadow-[0_0_8px_2px_theme(colors.primary/0.4)]"
    : ""

  return (
    <Comp
      data-slot="badge"
      className={cn(badgeVariants({ variant, color }), glowClass, className)}
      {...props}
    />
  )
}

export { Badge, badgeVariants }
