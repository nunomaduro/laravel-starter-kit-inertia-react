import * as React from "react"

import { cn } from "@/lib/utils"

interface NumberScrubberProps {
  value?: number
  onChange?: (value: number) => void
  min?: number
  max?: number
  step?: number
  precision?: number
  className?: string
  disabled?: boolean
  id?: string
  suffix?: string
  prefix?: string
}

function NumberScrubber({
  value = 0,
  onChange,
  min = -Infinity,
  max = Infinity,
  step = 1,
  precision = 0,
  className,
  disabled,
  id,
  suffix,
  prefix,
}: NumberScrubberProps) {
  const [isDragging, setIsDragging] = React.useState(false)
  const [isEditing, setIsEditing] = React.useState(false)
  const [inputValue, setInputValue] = React.useState(String(value))
  const startXRef = React.useRef(0)
  const startValueRef = React.useRef(value)
  const containerRef = React.useRef<HTMLDivElement>(null)

  React.useEffect(() => {
    if (!isEditing) {
      setInputValue(value.toFixed(precision))
    }
  }, [value, precision, isEditing])

  const clamp = React.useCallback(
    (v: number) => Math.min(max, Math.max(min, v)),
    [min, max]
  )

  const handleMouseDown = (e: React.MouseEvent) => {
    if (disabled || isEditing) return
    setIsDragging(true)
    startXRef.current = e.clientX
    startValueRef.current = value
    e.preventDefault()
  }

  React.useEffect(() => {
    if (!isDragging) return

    const handleMouseMove = (e: MouseEvent) => {
      const delta = (e.clientX - startXRef.current) * step
      const newValue = clamp(
        parseFloat((startValueRef.current + delta).toFixed(precision))
      )
      onChange?.(newValue)
    }

    const handleMouseUp = () => {
      setIsDragging(false)
    }

    window.addEventListener("mousemove", handleMouseMove)
    window.addEventListener("mouseup", handleMouseUp)
    return () => {
      window.removeEventListener("mousemove", handleMouseMove)
      window.removeEventListener("mouseup", handleMouseUp)
    }
  }, [isDragging, step, precision, clamp, onChange])

  const handleDoubleClick = () => {
    if (disabled) return
    setIsEditing(true)
    setInputValue(String(value))
  }

  const commitEdit = () => {
    setIsEditing(false)
    const parsed = parseFloat(inputValue)
    if (!isNaN(parsed)) {
      onChange?.(clamp(parseFloat(parsed.toFixed(precision))))
    }
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") commitEdit()
    if (e.key === "Escape") {
      setIsEditing(false)
      setInputValue(value.toFixed(precision))
    }
  }

  return (
    <div
      ref={containerRef}
      data-slot="number-scrubber"
      id={id}
      className={cn(
        "inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm transition-colors select-none",
        isDragging ? "cursor-ew-resize" : !isEditing ? "cursor-ew-resize" : "",
        disabled && "cursor-not-allowed opacity-50",
        className
      )}
      onMouseDown={handleMouseDown}
      onDoubleClick={handleDoubleClick}
    >
      {prefix && <span className="mr-1 text-muted-foreground">{prefix}</span>}
      {isEditing ? (
        <input
          type="number"
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value)}
          onBlur={commitEdit}
          onKeyDown={handleKeyDown}
          className="w-16 bg-transparent text-center outline-none"
          autoFocus
        />
      ) : (
        <span>{parseFloat(value.toFixed(precision))}</span>
      )}
      {suffix && <span className="ml-1 text-muted-foreground">{suffix}</span>}
    </div>
  )
}

export { NumberScrubber }
