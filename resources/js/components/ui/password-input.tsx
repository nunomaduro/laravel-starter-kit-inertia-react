import * as React from "react"
import { EyeIcon, EyeOffIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface PasswordInputProps extends Omit<React.ComponentProps<"input">, "type"> {
  showToggle?: boolean
}

function PasswordInput({ className, showToggle = true, ...props }: PasswordInputProps) {
  const [visible, setVisible] = React.useState(false)

  return (
    <div
      data-slot="password-input"
      className={cn(
        "border-input bg-background flex h-9 w-full items-center rounded-md border px-3 shadow-xs transition-colors",
        "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
        "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
        "has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-50",
        className,
      )}
    >
      <input
        type={visible ? "text" : "password"}
        data-slot="input"
        className="flex-1 min-w-0 bg-transparent text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed"
        {...props}
      />
      {showToggle && (
        <button
          type="button"
          onClick={() => setVisible((v) => !v)}
          className="ml-2 shrink-0 text-muted-foreground hover:text-foreground transition-colors focus-visible:outline-none"
          aria-label={visible ? "Hide password" : "Show password"}
          tabIndex={-1}
        >
          {visible ? <EyeOffIcon className="size-4" /> : <EyeIcon className="size-4" />}
        </button>
      )}
    </div>
  )
}

export { PasswordInput }
