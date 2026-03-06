import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const bottomNavVariants = cva(
  "fixed bottom-0 left-0 right-0 z-50 flex items-center border-t bg-background px-2 safe-area-pb",
  {
    variants: {
      size: {
        sm: "h-14",
        md: "h-16",
        lg: "h-20",
      },
    },
    defaultVariants: {
      size: "md",
    },
  }
)

interface BottomNavProps
  extends React.HTMLAttributes<HTMLElement>,
    VariantProps<typeof bottomNavVariants> {}

function BottomNav({ className, size, ...props }: BottomNavProps) {
  return (
    <nav
      data-slot="bottom-nav"
      className={cn(bottomNavVariants({ size }), className)}
      {...props}
    />
  )
}

interface BottomNavItemProps extends React.HTMLAttributes<HTMLButtonElement> {
  icon: React.ReactNode
  label: string
  isActive?: boolean
  badge?: string | number
  asChild?: boolean
}

function BottomNavItem({
  className,
  icon,
  label,
  isActive,
  badge,
  onClick,
  ...props
}: BottomNavItemProps) {
  return (
    <button
      data-slot="bottom-nav-item"
      data-active={isActive || undefined}
      onClick={onClick}
      className={cn(
        "relative flex flex-1 flex-col items-center justify-center gap-1 py-2 text-xs transition-colors",
        "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1",
        isActive ? "text-primary" : "text-muted-foreground hover:text-foreground",
        className
      )}
      {...props}
    >
      <div className="relative">
        <span className="flex items-center justify-center [&>svg]:size-5">{icon}</span>
        {badge !== undefined && (
          <span className="absolute -right-2 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-medium text-destructive-foreground">
            {badge}
          </span>
        )}
      </div>
      <span className="truncate">{label}</span>
    </button>
  )
}

export { BottomNav, BottomNavItem, bottomNavVariants }
export type { BottomNavProps, BottomNavItemProps }
