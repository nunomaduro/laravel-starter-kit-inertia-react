import * as React from "react"

import { cn } from "@/lib/utils"
import { Spinner } from "@/components/ui/spinner"

interface SplashScreenProps extends React.HTMLAttributes<HTMLDivElement> {
  visible?: boolean
  logo?: React.ReactNode
  message?: string
  /** Show an animated spinner below the logo */
  showSpinner?: boolean
  /** Optional progress 0–100; renders a thin progress bar at the bottom */
  progress?: number
}

function SplashScreen({
  visible = true,
  logo,
  message,
  showSpinner = true,
  progress,
  className,
  ...props
}: SplashScreenProps) {
  const [mounted, setMounted] = React.useState(false)

  React.useEffect(() => {
    if (visible) {
      setMounted(true)
    } else {
      const t = setTimeout(() => setMounted(false), 300)
      return () => clearTimeout(t)
    }
  }, [visible])

  if (!mounted) return null

  return (
    <div
      data-slot="splash-screen"
      className={cn(
        "fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-background transition-opacity duration-300",
        visible ? "opacity-100" : "opacity-0",
        className,
      )}
      aria-live="polite"
      aria-busy={visible}
      {...props}
    >
      <div className="flex flex-col items-center gap-6">
        {logo && <div className="flex items-center justify-center">{logo}</div>}

        {showSpinner && <Spinner size="lg" label={message ?? "Loading..."} />}

        {message && (
          <p className="text-muted-foreground text-sm animate-pulse">{message}</p>
        )}
      </div>

      {progress !== undefined && (
        <div className="absolute bottom-0 left-0 h-1 w-full bg-muted">
          <div
            className="h-full bg-primary transition-all duration-300"
            style={{ width: `${Math.min(100, Math.max(0, progress))}%` }}
          />
        </div>
      )}
    </div>
  )
}

export { SplashScreen }
export type { SplashScreenProps }
