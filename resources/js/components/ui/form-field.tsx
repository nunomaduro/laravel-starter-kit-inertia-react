import * as React from "react"

import { cn } from "@/lib/utils"
import { Label } from "@/components/ui/label"

interface FormFieldProps {
  label?: string
  description?: string
  error?: string
  hint?: string
  required?: boolean
  htmlFor?: string
  className?: string
  children: React.ReactNode
  horizontal?: boolean
  /** Renders next to the label (e.g. "Forgot password?" link). */
  labelAction?: React.ReactNode
}

function FormField({
  label,
  description,
  error,
  hint,
  required,
  htmlFor,
  className,
  children,
  horizontal = false,
  labelAction,
}: FormFieldProps) {
  return (
    <div
      data-slot="form-field"
      className={cn(
        horizontal ? "flex items-start gap-4" : "flex flex-col gap-1.5",
        className
      )}
    >
      {label && (
        <div className={cn(horizontal && "w-40 shrink-0 pt-2")}>
          <div className={cn(labelAction && "flex items-center justify-between gap-2")}>
            <Label htmlFor={htmlFor} className={cn(required && "after:ml-0.5 after:text-destructive after:content-['*']")}>
              {label}
            </Label>
            {labelAction}
          </div>
          {description && (
            <p className="text-muted-foreground mt-0.5 text-xs">{description}</p>
          )}
        </div>
      )}
      <div className={cn("flex flex-col gap-1", horizontal && "flex-1")}>
        {children}
        {error && (
          <p data-slot="form-field-error" className="text-destructive text-xs font-medium" role="alert">
            {error}
          </p>
        )}
        {hint && !error && (
          <p data-slot="form-field-hint" className="text-muted-foreground text-xs">
            {hint}
          </p>
        )}
      </div>
    </div>
  )
}

export { FormField }
