import * as React from "react"

import { cn } from "@/lib/utils"

type InputVariant = "outlined" | "filled" | "soft"
type InputSize = "xs" | "sm" | "md" | "lg"

const inputSizeClasses: Record<InputSize, string> = {
  xs: "h-6 px-2 text-xs rounded",
  sm: "h-7 px-2.5 text-sm rounded-md",
  md: "h-9 px-3 text-sm rounded-md",
  lg: "h-11 px-4 text-base rounded-md",
}

const inputVariantClasses: Record<InputVariant, string> = {
  outlined: "border border-input bg-transparent dark:bg-input/30",
  filled: "border border-transparent bg-muted dark:bg-muted/50",
  soft: "border border-transparent bg-primary/5 dark:bg-primary/10",
}

function Input({
  className,
  type,
  variant = "outlined",
  size = "md",
  startContent,
  endContent,
  ...props
}: Omit<React.ComponentProps<"input">, "size"> & {
  variant?: InputVariant
  /** Visual size: "xs" | "sm" | "md" | "lg". Overrides native HTML size attribute. */
  size?: InputSize | number
  startContent?: React.ReactNode
  endContent?: React.ReactNode
}) {
  const resolvedSize: InputSize =
    typeof size === "string" && (size === "xs" || size === "sm" || size === "md" || size === "lg")
      ? size
      : "md"

  if (startContent || endContent) {
    return (
      <div
        data-slot="input-wrapper"
        className={cn(
          "file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground transition-[color,box-shadow] outline-none shadow-xs w-full min-w-0 flex items-center gap-2",
          inputVariantClasses[variant],
          inputSizeClasses[resolvedSize],
          "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
          "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
          "has-[:disabled]:pointer-events-none has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-50",
        )}
      >
        {startContent && (
          <span className="text-muted-foreground shrink-0">{startContent}</span>
        )}
        <input
          type={type}
          data-slot="input"
          className="flex-1 min-w-0 bg-transparent outline-none placeholder:text-muted-foreground text-sm md:text-sm disabled:pointer-events-none"
          {...props}
        />
        {endContent && (
          <span className="text-muted-foreground shrink-0">{endContent}</span>
        )}
      </div>
    )
  }

  return (
    <input
      type={type}
      data-slot="input"
      className={cn(
        "file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground w-full min-w-0 shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
        "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
        "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
        inputVariantClasses[variant],
        inputSizeClasses[resolvedSize],
        className
      )}
      {...props}
    />
  )
}

export { Input }
