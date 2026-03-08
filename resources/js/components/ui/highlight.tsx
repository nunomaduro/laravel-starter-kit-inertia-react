import * as React from "react"
import { CheckIcon, CopyIcon } from "lucide-react"
import SyntaxHighlighter from "react-syntax-highlighter"
import { atomOneDark, atomOneLight } from "react-syntax-highlighter/dist/cjs/styles/hljs"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

interface HighlightProps {
  code: string
  language?: string
  showLineNumbers?: boolean
  showCopyButton?: boolean
  fileName?: string
  theme?: "dark" | "light" | "auto"
  maxHeight?: string
  className?: string
  wrapLongLines?: boolean
}

function Highlight({
  code,
  language = "text",
  showLineNumbers = false,
  showCopyButton = true,
  fileName,
  theme = "auto",
  maxHeight,
  className,
  wrapLongLines = false,
}: HighlightProps) {
  const [copied, setCopied] = React.useState(false)
  const isDark =
    theme === "dark" ||
    (theme === "auto" && typeof document !== "undefined" && document.documentElement.classList.contains("dark"))
  const style = isDark ? atomOneDark : atomOneLight

  const handleCopy = async () => {
    await navigator.clipboard.writeText(code)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <div
      data-slot="highlight"
      className={cn(
        "group relative overflow-hidden rounded-lg border bg-[#282c34] font-mono text-sm",
        className
      )}
    >
      {(fileName ?? showCopyButton) && (
        <div className="flex items-center justify-between border-b border-white/10 bg-black/20 px-4 py-2">
          {fileName && (
            <span className="text-xs text-white/60">{fileName}</span>
          )}
          {showCopyButton && (
            <Button
              variant="ghost"
              size="icon"
              className="ml-auto size-6 text-white/60 hover:text-white"
              onClick={() => void handleCopy()}
            >
              {copied ? (
                <CheckIcon className="size-3.5 text-green-400" />
              ) : (
                <CopyIcon className="size-3.5" />
              )}
              <span className="sr-only">Copy code</span>
            </Button>
          )}
        </div>
      )}
      {!fileName && showCopyButton && (
        <div className="absolute right-2 top-2 z-10 opacity-0 transition-opacity group-hover:opacity-100">
          <Button
            variant="ghost"
            size="icon"
            className="size-6 text-white/60 hover:text-white"
            onClick={() => void handleCopy()}
          >
            {copied ? (
              <CheckIcon className="size-3.5 text-green-400" />
            ) : (
              <CopyIcon className="size-3.5" />
            )}
            <span className="sr-only">Copy code</span>
          </Button>
        </div>
      )}
      <div style={maxHeight ? { maxHeight, overflowY: "auto" } : undefined}>
        <SyntaxHighlighter
          language={language}
          style={style}
          showLineNumbers={showLineNumbers}
          wrapLongLines={wrapLongLines}
          customStyle={{
            margin: 0,
            padding: "1rem",
            background: "transparent",
            fontSize: "0.875rem",
          }}
        >
          {code}
        </SyntaxHighlighter>
      </div>
    </div>
  )
}

export { Highlight }
