import * as React from "react"
import { LockIcon } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { cn } from "@/lib/utils"

interface FeatureGateProps {
  hasAccess: boolean
  feature?: string
  title?: string
  description?: string
  ctaLabel?: string
  onUpgrade?: () => void
  children: React.ReactNode
  className?: string
}

function FeatureGate({
  hasAccess,
  title = "Upgrade Required",
  description = "This feature is not available on your current plan.",
  ctaLabel = "Upgrade Plan",
  onUpgrade,
  children,
  className,
}: FeatureGateProps) {
  if (hasAccess) {
    return <>{children}</>
  }

  return (
    <div className={cn("flex items-center justify-center p-8", className)}>
      <Card className="max-w-sm w-full text-center">
        <CardHeader className="items-center">
          <div className="flex size-12 items-center justify-center rounded-full bg-muted mb-2">
            <LockIcon className="size-5 text-muted-foreground" />
          </div>
          <CardTitle>{title}</CardTitle>
          <CardDescription>{description}</CardDescription>
        </CardHeader>
        {onUpgrade && (
          <CardContent>
            <Button onClick={onUpgrade} className="w-full">
              {ctaLabel}
            </Button>
          </CardContent>
        )}
      </Card>
    </div>
  )
}

export { FeatureGate }
export type { FeatureGateProps }
