import * as React from "react"
import { SearchIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface SearchInputProps extends Omit<React.ComponentProps<"input">, "type"> {
  onClear?: () => void
  showClear?: boolean
  loading?: boolean
}

function SearchInput({
  className,
  onClear,
  showClear = true,
  loading = false,
  onChange,
  value,
  defaultValue,
  ...props
}: SearchInputProps) {
  const [internalValue, setInternalValue] = React.useState(defaultValue ?? "")
  const isControlled = value !== undefined
  const current = isControlled ? value : internalValue

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!isControlled) setInternalValue(e.target.value)
    onChange?.(e)
  }

  const handleClear = () => {
    if (!isControlled) setInternalValue("")
    onClear?.()
    const syntheticEvent = { target: { value: "" } } as React.ChangeEvent<HTMLInputElement>
    onChange?.(syntheticEvent)
  }

  return (
    <div
      data-slot="search-input"
      className={cn(
        "border-input bg-background flex h-9 w-full items-center gap-2 rounded-md border px-3 shadow-xs transition-colors",
        "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
        "has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-50",
        className
      )}
    >
      {loading ? (
        <span className="size-4 shrink-0 animate-spin rounded-full border-2 border-muted border-t-foreground" />
      ) : (
        <SearchIcon className="size-4 shrink-0 text-muted-foreground" />
      )}
      <input
        type="search"
        data-slot="input"
        value={current}
        onChange={handleChange}
        className="flex-1 min-w-0 bg-transparent text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed [&::-webkit-search-cancel-button]:hidden"
        {...props}
      />
      {showClear && current && String(current).length > 0 && (
        <button
          type="button"
          onClick={handleClear}
          className="shrink-0 text-muted-foreground hover:text-foreground transition-colors focus-visible:outline-none"
          aria-label="Clear search"
        >
          <XIcon className="size-3.5" />
        </button>
      )}
    </div>
  )
}

export { SearchInput }
