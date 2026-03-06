import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const stackVariants = cva("flex", {
  variants: {
    direction: {
      row: "flex-row",
      col: "flex-col",
      "row-reverse": "flex-row-reverse",
      "col-reverse": "flex-col-reverse",
    },
    align: {
      start: "items-start",
      center: "items-center",
      end: "items-end",
      stretch: "items-stretch",
      baseline: "items-baseline",
    },
    justify: {
      start: "justify-start",
      center: "justify-center",
      end: "justify-end",
      between: "justify-between",
      around: "justify-around",
      evenly: "justify-evenly",
    },
    wrap: {
      wrap: "flex-wrap",
      nowrap: "flex-nowrap",
      "wrap-reverse": "flex-wrap-reverse",
    },
    gap: {
      0: "gap-0",
      1: "gap-1",
      2: "gap-2",
      3: "gap-3",
      4: "gap-4",
      5: "gap-5",
      6: "gap-6",
      8: "gap-8",
      10: "gap-10",
      12: "gap-12",
    },
  },
  defaultVariants: {
    direction: "col",
    gap: 4,
  },
})

interface StackProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof stackVariants> {}

function Stack({ className, direction, align, justify, wrap, gap, ...props }: StackProps) {
  return (
    <div
      data-slot="stack"
      className={cn(stackVariants({ direction, align, justify, wrap, gap }), className)}
      {...props}
    />
  )
}

function VStack({ className, ...props }: Omit<StackProps, "direction">) {
  return (
    <Stack
      data-slot="vstack"
      direction="col"
      className={className}
      {...props}
    />
  )
}

function HStack({ className, ...props }: Omit<StackProps, "direction">) {
  return (
    <Stack
      data-slot="hstack"
      direction="row"
      align={props.align ?? "center"}
      className={className}
      {...props}
    />
  )
}

export { Stack, VStack, HStack, stackVariants }
export type { StackProps }
