import * as React from "react"

import { cn } from "@/lib/utils"

interface ButtonGroupProps extends React.HTMLAttributes<HTMLDivElement> {
  orientation?: "horizontal" | "vertical"
  /** When true, buttons are visually connected (rounded only on outer edges). */
  attached?: boolean
}

function ButtonGroup({
  className,
  orientation = "horizontal",
  attached = false,
  children,
  ...props
}: ButtonGroupProps) {
  return (
    <div
      data-slot="button-group"
      role="group"
      data-orientation={orientation}
      className={cn(
        "inline-flex",
        orientation === "vertical" ? "flex-col" : "flex-row",
        attached && orientation === "horizontal" && [
          "[&>*]:rounded-none",
          "[&>*:first-child]:rounded-l-md",
          "[&>*:last-child]:rounded-r-md",
          "[&>*:not(:first-child)]:-ml-px",
        ],
        attached && orientation === "vertical" && [
          "[&>*]:rounded-none",
          "[&>*:first-child]:rounded-t-md",
          "[&>*:last-child]:rounded-b-md",
          "[&>*:not(:first-child)]:-mt-px",
        ],
        !attached && orientation === "horizontal" && "gap-1",
        !attached && orientation === "vertical" && "gap-1",
        className,
      )}
      {...props}
    >
      {children}
    </div>
  )
}

export { ButtonGroup }
export type { ButtonGroupProps }
