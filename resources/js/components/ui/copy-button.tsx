import * as React from "react"
import { CheckIcon, CopyIcon } from "lucide-react"
import { type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"
import { buttonVariants } from "@/components/ui/button"

interface CopyButtonProps
  extends Omit<React.ComponentProps<"button">, "color" | "onClick" | "onCopy">,
    VariantProps<typeof buttonVariants> {
  color?: "primary" | "secondary" | "info" | "success" | "warning" | "error" | "neutral"
  /** The text to copy to clipboard. */
  value: string
  /** Duration (ms) to show the checkmark after copying. Default: 2000 */
  timeout?: number
  /** Accessible label. Default: "Copy" */
  label?: string
  onCopy?: (value: string) => void
}

function CopyButton({
  className,
  variant = "ghost",
  size = "icon",
  color,
  value,
  timeout = 2000,
  label = "Copy",
  onCopy,
  disabled,
  ...props
}: CopyButtonProps) {
  const [copied, setCopied] = React.useState(false)
  const timerRef = React.useRef<ReturnType<typeof setTimeout>>(undefined)

  const handleClick = async () => {
    if (copied || disabled) return
    try {
      await navigator.clipboard.writeText(value)
      setCopied(true)
      onCopy?.(value)
      clearTimeout(timerRef.current)
      timerRef.current = setTimeout(() => setCopied(false), timeout)
    } catch {
      // Clipboard API unavailable — silently ignore
    }
  }

  React.useEffect(() => {
    return () => clearTimeout(timerRef.current)
  }, [])

  return (
    <button
      type="button"
      data-slot="copy-button"
      data-copied={copied || undefined}
      aria-label={copied ? "Copied!" : label}
      title={copied ? "Copied!" : label}
      disabled={disabled}
      onClick={handleClick}
      className={cn(
        buttonVariants({ variant, size, color }),
        "relative transition-all",
        disabled && "pointer-events-none opacity-50",
        className,
      )}
      {...props}
    >
      <span
        className={cn(
          "absolute inset-0 flex items-center justify-center transition-all duration-200",
          copied ? "scale-100 opacity-100" : "scale-50 opacity-0",
        )}
        aria-hidden
      >
        <CheckIcon className="size-4 text-success" />
      </span>
      <span
        className={cn(
          "flex items-center justify-center transition-all duration-200",
          copied ? "scale-50 opacity-0" : "scale-100 opacity-100",
        )}
        aria-hidden
      >
        <CopyIcon className="size-4" />
      </span>
    </button>
  )
}

export { CopyButton }
export type { CopyButtonProps }
