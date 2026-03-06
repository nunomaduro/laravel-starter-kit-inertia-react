import * as React from "react"

import { cn } from "@/lib/utils"

type CardSkin = "shadow" | "bordered" | "flat" | "elevated"

function Card({
  className,
  skin,
  hoverable = false,
  ...props
}: React.ComponentProps<"div"> & {
  skin?: CardSkin
  hoverable?: boolean
}) {
  return (
    <div
      data-slot="card"
      data-skin={skin}
      className={cn(
        "bg-card text-card-foreground flex flex-col gap-6 rounded-xl py-6",
        // Default skin (shadow) — matches the original
        !skin || skin === "shadow" ? "border shadow-sm" : null,
        skin === "bordered" ? "border border-border shadow-none" : null,
        skin === "flat" ? "border-0 shadow-none bg-muted/50" : null,
        skin === "elevated" ? "border-0 shadow-lg" : null,
        // Respect data-card-skin attribute from theme system
        "[&[data-card-skin=shadow]]:border [&[data-card-skin=shadow]]:shadow-sm",
        "[&[data-card-skin=bordered]]:border [&[data-card-skin=bordered]]:shadow-none",
        "[&[data-card-skin=flat]]:border-0 [&[data-card-skin=flat]]:shadow-none",
        "[&[data-card-skin=elevated]]:border-0 [&[data-card-skin=elevated]]:shadow-lg",
        hoverable ? "transition-shadow hover:shadow-md cursor-pointer" : null,
        className
      )}
      {...props}
    />
  )
}

function CardHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-header"
      className={cn("flex flex-col gap-1.5 px-6", className)}
      {...props}
    />
  )
}

function CardTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-title"
      className={cn("leading-none font-semibold", className)}
      {...props}
    />
  )
}

function CardDescription({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-description"
      className={cn("text-muted-foreground text-sm", className)}
      {...props}
    />
  )
}

function CardContent({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-content"
      className={cn("px-6", className)}
      {...props}
    />
  )
}

function CardFooter({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-footer"
      className={cn("flex items-center px-6", className)}
      {...props}
    />
  )
}

export { Card, CardHeader, CardFooter, CardTitle, CardDescription, CardContent }
