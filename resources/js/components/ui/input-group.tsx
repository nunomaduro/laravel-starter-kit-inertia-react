import * as React from "react"

import { cn } from "@/lib/utils"

interface InputGroupProps extends React.HTMLAttributes<HTMLDivElement> {
  children: React.ReactNode
}

/**
 * InputGroup joins adjacent Input / Button / Select elements visually by
 * removing inner border-radii and applying a shared focus ring.
 *
 * Usage:
 *   <InputGroup>
 *     <InputGroupAddon>https://</InputGroupAddon>
 *     <Input placeholder="example.com" />
 *     <Button>Go</Button>
 *   </InputGroup>
 */
function InputGroup({ className, children, ...props }: InputGroupProps) {
  return (
    <div
      data-slot="input-group"
      className={cn(
        "flex w-full items-stretch",
        // Remove inner border-radii for all direct children except first/last.
        "[&>*:not(:first-child):not(:last-child)]:rounded-none",
        "[&>*:first-child]:rounded-r-none",
        "[&>*:last-child]:rounded-l-none",
        // Negative margin to collapse double borders between siblings.
        "[&>*:not(:first-child)]:-ml-px",
        // Bring focused element to front so its ring is visible.
        "[&>*:focus-within]:relative [&>*:focus-within]:z-10",
        className
      )}
      {...props}
    >
      {children}
    </div>
  )
}

function InputGroupAddon({ className, children, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      data-slot="input-group-addon"
      className={cn(
        "border-input bg-muted inline-flex items-center rounded-md border px-3 py-2 text-sm text-muted-foreground select-none shrink-0",
        className
      )}
      {...props}
    >
      {children}
    </div>
  )
}

export { InputGroup, InputGroupAddon }
