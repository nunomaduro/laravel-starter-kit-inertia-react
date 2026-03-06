import * as React from "react"
import { TrendingDownIcon, TrendingUpIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Skeleton } from "@/components/ui/skeleton"

interface StatCardProps {
  title: string
  value: React.ReactNode
  description?: React.ReactNode
  icon?: React.ReactNode
  trend?: {
    value: number
    label?: string
    direction?: "up" | "down" | "neutral"
  }
  badge?: React.ReactNode
  isLoading?: boolean
  className?: string
  onClick?: () => void
}

function StatCard({
  title,
  value,
  description,
  icon,
  trend,
  badge,
  isLoading = false,
  className,
  onClick,
}: StatCardProps) {
  const trendDirection =
    trend?.direction ?? (trend?.value !== undefined ? (trend.value >= 0 ? "up" : "down") : undefined)

  return (
    <Card
      data-slot="stat-card"
      className={cn(
        "relative overflow-hidden",
        onClick && "cursor-pointer transition-shadow hover:shadow-md",
        className
      )}
      onClick={onClick}
    >
      <CardContent className="p-5">
        <div className="flex items-start justify-between gap-4">
          <div className="min-w-0 flex-1">
            <p className="truncate text-sm font-medium text-muted-foreground">{title}</p>
            <div className="mt-1">
              {isLoading ? (
                <Skeleton className="h-8 w-24" />
              ) : (
                <p className="text-2xl font-bold tracking-tight">{value}</p>
              )}
            </div>
            {(description || trend) && (
              <div className="mt-2 flex flex-wrap items-center gap-2">
                {trend && (
                  <span
                    className={cn(
                      "flex items-center gap-0.5 text-xs font-medium",
                      trendDirection === "up" && "text-success",
                      trendDirection === "down" && "text-error",
                      trendDirection === "neutral" && "text-muted-foreground"
                    )}
                  >
                    {trendDirection === "up" && <TrendingUpIcon className="size-3" />}
                    {trendDirection === "down" && <TrendingDownIcon className="size-3" />}
                    {trend.value > 0 ? "+" : ""}
                    {trend.value}%
                    {trend.label && (
                      <span className="font-normal text-muted-foreground ml-1">
                        {trend.label}
                      </span>
                    )}
                  </span>
                )}
                {description && (
                  <span className="text-xs text-muted-foreground">{description}</span>
                )}
              </div>
            )}
          </div>
          <div className="flex shrink-0 flex-col items-end gap-2">
            {badge && <Badge variant="secondary">{badge}</Badge>}
            {icon && (
              <div className="rounded-lg bg-muted p-2 text-muted-foreground">
                {icon}
              </div>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  )
}

export { StatCard }
