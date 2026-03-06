import * as React from "react"

import { cn } from "@/lib/utils"

type ValidTags = keyof React.JSX.IntrinsicElements

interface BoxProps<T extends ValidTags = "div"> {
  as?: T
  className?: string
  children?: React.ReactNode
}

function Box<T extends ValidTags = "div">({
  as,
  className,
  children,
  ...props
}: BoxProps<T> & Omit<React.ComponentPropsWithoutRef<T>, keyof BoxProps<T>>) {
  const Component = (as ?? "div") as React.ElementType

  return (
    <Component
      data-slot="box"
      className={cn(className)}
      {...props}
    >
      {children}
    </Component>
  )
}

export { Box }
export type { BoxProps }
