import * as React from "react"

import { cn } from "@/lib/utils"
import { useReducedMotion } from "@/hooks/use-reduced-motion"

type SkeletonAnimation = "pulse" | "shimmer" | "wave"

const animationClasses: Record<SkeletonAnimation, string> = {
  pulse: "animate-pulse",
  shimmer:
    "relative overflow-hidden before:absolute before:inset-0 before:-translate-x-full before:animate-[shimmer_1.5s_infinite] before:bg-gradient-to-r before:from-transparent before:via-white/20 before:to-transparent",
  wave:
    "relative overflow-hidden before:absolute before:inset-0 before:-translate-x-full before:animate-[shimmer_2s_ease-in-out_infinite] before:bg-gradient-to-r before:from-transparent before:via-foreground/10 before:to-transparent",
}

function Skeleton({
  className,
  animation = "pulse",
  ...props
}: React.ComponentProps<"div"> & {
  animation?: SkeletonAnimation
}) {
  const reducedMotion = useReducedMotion()

  return (
    <div
      data-slot="skeleton"
      className={cn(
        "bg-primary/10 rounded-md",
        reducedMotion ? undefined : animationClasses[animation],
        className
      )}
      {...props}
    />
  )
}

export { Skeleton }
