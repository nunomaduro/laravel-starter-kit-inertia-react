import * as React from "react"
import { ChevronDownIcon } from "lucide-react"
import { type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"
import { Button, buttonVariants } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

interface SplitButtonProps
  extends Omit<React.ComponentProps<"button">, "color">,
    VariantProps<typeof buttonVariants> {
  color?: "primary" | "secondary" | "info" | "success" | "warning" | "error" | "neutral"
  /** Content for the dropdown menu (DropdownMenuItems etc.) */
  dropdownContent: React.ReactNode
  dropdownLabel?: string
  isLoading?: boolean
}

function SplitButton({
  className,
  variant = "default",
  size = "default",
  color,
  children,
  dropdownContent,
  dropdownLabel = "More options",
  isLoading,
  disabled,
  ...props
}: SplitButtonProps) {
  return (
    <div data-slot="split-button" className={cn("inline-flex", className)}>
      {/* Primary action */}
      <Button
        variant={variant}
        size={size}
        color={color}
        isLoading={isLoading}
        disabled={disabled}
        className="rounded-r-none border-r-0 focus-visible:z-10"
        {...props}
      >
        {children}
      </Button>

      {/* Dropdown trigger */}
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <button
            type="button"
            aria-label={dropdownLabel}
            disabled={disabled || isLoading}
            className={cn(
              buttonVariants({ variant, size, color }),
              "rounded-l-none border-l px-2 focus-visible:z-10 disabled:pointer-events-none disabled:opacity-50",
            )}
          >
            <ChevronDownIcon className="size-4" />
          </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          {dropdownContent}
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}

export { SplitButton }
export type { SplitButtonProps }
