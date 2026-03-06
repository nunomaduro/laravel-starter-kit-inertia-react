import * as React from "react"
import { HelpCircleIcon, InfoIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip"

interface ContextualHelpProps {
  content: React.ReactNode
  title?: string
  trigger?: "hover" | "click"
  icon?: "help" | "info"
  size?: "sm" | "md"
  className?: string
}

function ContextualHelp({
  content,
  title,
  trigger = "hover",
  icon = "help",
  size = "sm",
  className,
}: ContextualHelpProps) {
  const iconSize = size === "sm" ? "size-3.5" : "size-4"
  const IconComponent = icon === "info" ? InfoIcon : HelpCircleIcon

  const iconEl = (
    <IconComponent
      className={cn(
        iconSize,
        "text-muted-foreground hover:text-foreground cursor-help transition-colors",
        className
      )}
    />
  )

  if (trigger === "click") {
    return (
      <Popover>
        <PopoverTrigger asChild>
          <button
            type="button"
            className="inline-flex items-center focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring rounded-sm"
            aria-label="Help"
          >
            {iconEl}
          </button>
        </PopoverTrigger>
        <PopoverContent className="max-w-xs text-sm" side="top">
          {title && <p className="font-semibold mb-1">{title}</p>}
          <div className="text-muted-foreground">{content}</div>
        </PopoverContent>
      </Popover>
    )
  }

  return (
    <Tooltip>
      <TooltipTrigger asChild>
        <span
          role="button"
          tabIndex={0}
          className="inline-flex items-center focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring rounded-sm"
          aria-label="Help"
        >
          {iconEl}
        </span>
      </TooltipTrigger>
      <TooltipContent side="top" className="max-w-xs text-sm">
        {title && <p className="font-semibold mb-1">{title}</p>}
        <div>{content}</div>
      </TooltipContent>
    </Tooltip>
  )
}

export { ContextualHelp }
