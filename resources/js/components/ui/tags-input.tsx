import * as React from "react"
import { XIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface TagsInputProps {
  value?: string[]
  defaultValue?: string[]
  onChange?: (value: string[]) => void
  placeholder?: string
  disabled?: boolean
  maxTags?: number
  allowDuplicates?: boolean
  delimiter?: string
  className?: string
  inputClassName?: string
}

function TagsInput({
  value,
  defaultValue = [],
  onChange,
  placeholder = "Add tag...",
  disabled = false,
  maxTags,
  allowDuplicates = false,
  delimiter = ",",
  className,
  inputClassName,
}: TagsInputProps) {
  const [internalTags, setInternalTags] = React.useState<string[]>(defaultValue)
  const [inputValue, setInputValue] = React.useState("")
  const inputRef = React.useRef<HTMLInputElement>(null)

  const isControlled = value !== undefined
  const tags = isControlled ? value : internalTags

  const updateTags = (next: string[]) => {
    if (!isControlled) {
      setInternalTags(next)
    }
    onChange?.(next)
  }

  const addTag = (tag: string) => {
    const trimmed = tag.trim()
    if (!trimmed) return
    if (!allowDuplicates && tags.includes(trimmed)) return
    if (maxTags !== undefined && tags.length >= maxTags) return
    updateTags([...tags, trimmed])
    setInputValue("")
  }

  const removeTag = (index: number) => {
    updateTags(tags.filter((_, i) => i !== index))
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" || e.key === delimiter) {
      e.preventDefault()
      addTag(inputValue)
    } else if (e.key === "Backspace" && !inputValue && tags.length > 0) {
      removeTag(tags.length - 1)
    }
  }

  const handlePaste = (e: React.ClipboardEvent<HTMLInputElement>) => {
    const pasted = e.clipboardData.getData("text")
    const parts = pasted.split(delimiter).map((s) => s.trim()).filter(Boolean)
    if (parts.length > 1) {
      e.preventDefault()
      const next = allowDuplicates
        ? [...tags, ...parts]
        : [...tags, ...parts.filter((p) => !tags.includes(p))]
      const limited = maxTags !== undefined ? next.slice(0, maxTags) : next
      updateTags(limited)
    }
  }

  return (
    <div
      data-slot="tags-input"
      className={cn(
        "border-input bg-background flex min-h-9 w-full flex-wrap items-center gap-1.5 rounded-md border px-2 py-1.5 text-sm shadow-xs transition-colors",
        "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
        "has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-50",
        className
      )}
      onClick={() => inputRef.current?.focus()}
    >
      {tags.map((tag, index) => (
        <span
          key={index}
          className="bg-secondary text-secondary-foreground inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
        >
          {tag}
          {!disabled && (
            <button
              type="button"
              onClick={() => removeTag(index)}
              className="inline-flex size-3.5 items-center justify-center rounded-full opacity-60 hover:opacity-100"
              aria-label={`Remove ${tag}`}
            >
              <XIcon className="size-2.5" />
            </button>
          )}
        </span>
      ))}
      <input
        ref={inputRef}
        value={inputValue}
        onChange={(e) => setInputValue(e.target.value)}
        onKeyDown={handleKeyDown}
        onPaste={handlePaste}
        onBlur={() => addTag(inputValue)}
        placeholder={tags.length === 0 ? placeholder : undefined}
        disabled={disabled || (maxTags !== undefined && tags.length >= maxTags)}
        className={cn(
          "min-w-20 flex-1 bg-transparent placeholder:text-muted-foreground focus:outline-none disabled:cursor-not-allowed",
          inputClassName
        )}
      />
    </div>
  )
}

export { TagsInput }
