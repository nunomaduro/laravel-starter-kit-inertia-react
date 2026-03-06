import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const scrollShadowVariants = cva("relative overflow-auto", {
  variants: {
    direction: {
      vertical: "flex flex-col",
      horizontal: "flex flex-row",
      both: "",
    },
    size: {
      sm: "[--scroll-shadow-size:40px]",
      md: "[--scroll-shadow-size:80px]",
      lg: "[--scroll-shadow-size:120px]",
    },
  },
  defaultVariants: {
    direction: "vertical",
    size: "md",
  },
})

interface ScrollShadowProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof scrollShadowVariants> {
  hideScrollBar?: boolean
}

function ScrollShadow({
  className,
  direction = "vertical",
  size = "md",
  hideScrollBar = false,
  style,
  ...props
}: ScrollShadowProps) {
  const maskStyles: React.CSSProperties = {}

  if (direction === "vertical" || direction === "both") {
    maskStyles.maskImage =
      "linear-gradient(to bottom, transparent 0%, black var(--scroll-shadow-size), black calc(100% - var(--scroll-shadow-size)), transparent 100%)"
    maskStyles.WebkitMaskImage = maskStyles.maskImage
  }

  if (direction === "horizontal") {
    maskStyles.maskImage =
      "linear-gradient(to right, transparent 0%, black var(--scroll-shadow-size), black calc(100% - var(--scroll-shadow-size)), transparent 100%)"
    maskStyles.WebkitMaskImage = maskStyles.maskImage
  }

  if (direction === "both") {
    maskStyles.maskImage =
      "linear-gradient(to bottom, transparent 0%, black var(--scroll-shadow-size), black calc(100% - var(--scroll-shadow-size)), transparent 100%), linear-gradient(to right, transparent 0%, black var(--scroll-shadow-size), black calc(100% - var(--scroll-shadow-size)), transparent 100%)"
    maskStyles.maskComposite = "intersect"
    maskStyles.WebkitMaskImage = maskStyles.maskImage
    maskStyles.WebkitMaskComposite = "source-in"
  }

  return (
    <div
      data-slot="scroll-shadow"
      className={cn(
        scrollShadowVariants({ direction, size }),
        hideScrollBar && "scrollbar-hide [&::-webkit-scrollbar]:hidden",
        className
      )}
      style={{ ...maskStyles, ...style }}
      {...props}
    />
  )
}

export { ScrollShadow, scrollShadowVariants }
export type { ScrollShadowProps }
