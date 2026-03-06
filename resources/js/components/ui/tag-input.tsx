import * as React from "react"
import { XIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Badge } from "@/components/ui/badge"

interface TagInputProps {
  value?: string[]
  onChange?: (tags: string[]) => void
  placeholder?: string
  maxTags?: number
  delimiters?: string[]
  className?: string
  disabled?: boolean
  id?: string
}

function TagInput({
  value = [],
  onChange,
  placeholder = "Add a tag...",
  maxTags,
  delimiters = ["Enter", ","],
  className,
  disabled,
  id,
}: TagInputProps) {
  const [inputValue, setInputValue] = React.useState("")
  const inputRef = React.useRef<HTMLInputElement>(null)

  const addTag = (tag: string) => {
    const trimmed = tag.trim()
    if (!trimmed) return
    if (value.includes(trimmed)) return
    if (maxTags !== undefined && value.length >= maxTags) return
    onChange?.([...value, trimmed])
    setInputValue("")
  }

  const removeTag = (tagToRemove: string) => {
    onChange?.(value.filter((t) => t !== tagToRemove))
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (delimiters.includes(e.key)) {
      e.preventDefault()
      addTag(inputValue)
    } else if (e.key === "Backspace" && inputValue === "" && value.length > 0) {
      onChange?.(value.slice(0, -1))
    }
  }

  const handleBlur = () => {
    if (inputValue) addTag(inputValue)
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value
    if (val.endsWith(",")) {
      addTag(val.slice(0, -1))
    } else {
      setInputValue(val)
    }
  }

  return (
    <div
      data-slot="tag-input"
      className={cn(
        "flex min-h-9 flex-wrap gap-1.5 rounded-md border border-input bg-background px-3 py-1.5 text-sm focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
        disabled && "cursor-not-allowed opacity-50",
        className
      )}
      onClick={() => inputRef.current?.focus()}
    >
      {value.map((tag) => (
        <Badge key={tag} variant="secondary" className="gap-1 pr-1">
          {tag}
          {!disabled && (
            <button
              type="button"
              className="cursor-pointer rounded-sm opacity-60 hover:opacity-100"
              onClick={(e) => {
                e.stopPropagation()
                removeTag(tag)
              }}
            >
              <XIcon className="size-3" />
              <span className="sr-only">Remove {tag}</span>
            </button>
          )}
        </Badge>
      ))}
      <input
        ref={inputRef}
        id={id}
        type="text"
        value={inputValue}
        onChange={handleChange}
        onKeyDown={handleKeyDown}
        onBlur={handleBlur}
        placeholder={value.length === 0 ? placeholder : ""}
        disabled={disabled}
        className="min-w-24 flex-1 bg-transparent outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed"
      />
    </div>
  )
}

export { TagInput }
