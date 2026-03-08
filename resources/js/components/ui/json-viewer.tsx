import * as React from "react"
import { CheckIcon, ChevronRightIcon, CopyIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

type JsonValue =
  | string
  | number
  | boolean
  | null
  | JsonValue[]
  | { [key: string]: JsonValue }

interface JsonViewerProps {
  value: unknown
  defaultExpanded?: boolean
  maxDepth?: number
  className?: string
}

function JsonViewer({
  value,
  defaultExpanded = true,
  maxDepth = 3,
  className,
}: JsonViewerProps) {
  const [copied, setCopied] = React.useState(false)

  const handleCopy = async () => {
    await navigator.clipboard.writeText(JSON.stringify(value, null, 2))
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <div
      data-slot="json-viewer"
      className={cn(
        "group relative overflow-auto rounded-lg border bg-muted/30 p-4 font-mono text-sm",
        className
      )}
    >
      <div className="absolute right-2 top-2 opacity-0 transition-opacity group-hover:opacity-100">
        <Button
          variant="ghost"
          size="icon"
          className="size-6"
          onClick={() => void handleCopy()}
        >
          {copied ? (
            <CheckIcon className="size-3.5 text-green-500" />
          ) : (
            <CopyIcon className="size-3.5" />
          )}
          <span className="sr-only">Copy JSON</span>
        </Button>
      </div>
      <JsonNode
        value={value as JsonValue}
        depth={0}
        maxDepth={maxDepth}
        defaultExpanded={defaultExpanded}
        isRoot
      />
    </div>
  )
}

function JsonNode({
  value,
  depth,
  maxDepth,
  defaultExpanded,
  isRoot: _isRoot = false,
  keyName,
}: {
  value: JsonValue
  depth: number
  maxDepth: number
  defaultExpanded: boolean
  isRoot?: boolean
  keyName?: string
}) {
  void _isRoot;
  const [expanded, setExpanded] = React.useState(defaultExpanded && depth < maxDepth)

  const isObject = typeof value === "object" && value !== null && !Array.isArray(value)
  const isArray = Array.isArray(value)
  const isExpandable = isObject || isArray

  const entries = isObject
    ? Object.entries(value as Record<string, JsonValue>)
    : isArray
      ? (value as JsonValue[]).map((v, i) => [String(i), v] as [string, JsonValue])
      : []

  const bracket = isArray ? ["[", "]"] : ["{", "}"]
  const count = entries.length

  return (
    <span className="block">
      {keyName !== undefined && (
        <span className="text-blue-500 dark:text-blue-400">&quot;{keyName}&quot;</span>
      )}
      {keyName !== undefined && <span className="text-muted-foreground">: </span>}
      {isExpandable ? (
        <>
          <button
            type="button"
            onClick={() => setExpanded((e) => !e)}
            className="inline-flex items-center gap-0.5 hover:text-foreground"
          >
            <ChevronRightIcon
              className={cn(
                "size-3 text-muted-foreground transition-transform",
                expanded && "rotate-90"
              )}
            />
            <span className="text-muted-foreground">{bracket[0]}</span>
          </button>
          {!expanded && (
            <span className="text-muted-foreground">
              {count} {count === 1 ? "item" : "items"}
              {bracket[1]}
            </span>
          )}
          {expanded && (
            <span className="block pl-4">
              {entries.map(([k, v], i) => (
                <span key={k} className="block">
                  <JsonNode
                    value={v}
                    depth={depth + 1}
                    maxDepth={maxDepth}
                    defaultExpanded={defaultExpanded}
                    keyName={isObject ? k : undefined}
                  />
                  {i < entries.length - 1 && (
                    <span className="text-muted-foreground">,</span>
                  )}
                </span>
              ))}
              <span className="text-muted-foreground">{bracket[1]}</span>
            </span>
          )}
        </>
      ) : (
        <JsonPrimitive value={value} />
      )}
    </span>
  )
}

function JsonPrimitive({ value }: { value: JsonValue }) {
  if (value === null) {
    return <span className="text-orange-500 dark:text-orange-400">null</span>
  }
  if (typeof value === "boolean") {
    return (
      <span className="text-purple-600 dark:text-purple-400">
        {String(value)}
      </span>
    )
  }
  if (typeof value === "number") {
    return (
      <span className="text-green-600 dark:text-green-400">{String(value)}</span>
    )
  }
  return (
    <span className="text-amber-600 dark:text-amber-400">
      &quot;{String(value)}&quot;
    </span>
  )
}

export { JsonViewer }
