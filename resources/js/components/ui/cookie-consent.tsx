import * as React from "react"
import { CookieIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

interface CookieConsentProps {
  onAccept?: () => void
  onDecline?: () => void
  onCustomize?: () => void
  message?: React.ReactNode
  privacyPolicyUrl?: string
  className?: string
  variant?: "banner" | "floating"
  position?: "bottom" | "top" | "bottom-left" | "bottom-right"
}

function CookieConsent({
  onAccept,
  onDecline,
  onCustomize,
  message,
  privacyPolicyUrl,
  className,
  variant = "banner",
  position = "bottom",
}: CookieConsentProps) {
  const [visible, setVisible] = React.useState(true)

  if (!visible) return null

  const handleAccept = () => {
    setVisible(false)
    onAccept?.()
  }

  const handleDecline = () => {
    setVisible(false)
    onDecline?.()
  }

  const positionClass: Record<string, string> = {
    bottom: "fixed bottom-0 left-0 right-0",
    top: "fixed top-0 left-0 right-0",
    "bottom-left": "fixed bottom-4 left-4",
    "bottom-right": "fixed bottom-4 right-4",
  }

  const defaultMessage = (
    <span>
      We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.{" "}
      {privacyPolicyUrl && (
        <a
          href={privacyPolicyUrl}
          className="underline hover:no-underline"
          target="_blank"
          rel="noopener noreferrer"
        >
          Learn more
        </a>
      )}
    </span>
  )

  return (
    <div
      data-slot="cookie-consent"
      data-variant={variant}
      className={cn(
        "z-50 bg-background border shadow-lg",
        variant === "banner" ? "p-4" : "max-w-sm rounded-xl p-4",
        positionClass[position] ?? positionClass.bottom,
        className
      )}
      role="region"
      aria-label="Cookie consent"
    >
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="flex items-start gap-3">
          <CookieIcon className="mt-0.5 size-5 shrink-0 text-muted-foreground" />
          <p className="text-sm text-muted-foreground">
            {message ?? defaultMessage}
          </p>
        </div>
        <div className="flex shrink-0 flex-wrap items-center gap-2 sm:ml-auto">
          {onCustomize && (
            <Button
              variant="outline"
              size="sm"
              onClick={onCustomize}
            >
              Customize
            </Button>
          )}
          <Button
            variant="outline"
            size="sm"
            onClick={handleDecline}
          >
            Decline
          </Button>
          <Button size="sm" onClick={handleAccept}>
            Accept All
          </Button>
        </div>
      </div>
      <button
        type="button"
        className="absolute right-3 top-3 rounded-xs text-muted-foreground opacity-70 hover:opacity-100"
        onClick={() => setVisible(false)}
        aria-label="Dismiss"
      >
        <XIcon className="size-4" />
      </button>
    </div>
  )
}

export { CookieConsent }
