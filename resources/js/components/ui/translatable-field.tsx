import * as React from "react"

import { cn } from "@/lib/utils"

interface TranslatableFieldProps {
  locales: string[]
  value?: Record<string, string>
  defaultValue?: Record<string, string>
  onChange?: (value: Record<string, string>) => void
  renderInput: (props: { locale: string; value: string; onChange: (val: string) => void }) => React.ReactNode
  className?: string
  tabClassName?: string
}

function TranslatableField({
  locales,
  value,
  defaultValue,
  onChange,
  renderInput,
  className,
  tabClassName,
}: TranslatableFieldProps) {
  const [activeLocale, setActiveLocale] = React.useState(locales[0] ?? "")
  const isControlled = value !== undefined
  const initial = defaultValue ?? locales.reduce<Record<string, string>>((acc, l) => ({ ...acc, [l]: "" }), {})
  const [internalValue, setInternalValue] = React.useState<Record<string, string>>(initial)
  const current = isControlled ? value : internalValue

  const handleChange = (locale: string, val: string) => {
    const next = { ...current, [locale]: val }
    if (!isControlled) setInternalValue(next)
    onChange?.(next)
  }

  return (
    <div data-slot="translatable-field" className={cn("space-y-2", className)}>
      <div className="flex items-center gap-0.5 border-b">
        {locales.map((locale) => (
          <button
            key={locale}
            type="button"
            onClick={() => setActiveLocale(locale)}
            className={cn(
              "relative -mb-px px-3 py-1.5 text-xs font-medium uppercase tracking-wide transition-colors focus-visible:outline-none",
              activeLocale === locale
                ? "border-b-2 border-primary text-primary"
                : "text-muted-foreground hover:text-foreground",
              tabClassName
            )}
          >
            {locale}
            {current[locale] ? (
              <span className="ml-1 inline-block size-1.5 rounded-full bg-emerald-500" aria-hidden />
            ) : null}
          </button>
        ))}
      </div>
      {locales.map((locale) => (
        <div key={locale} className={cn(locale !== activeLocale && "hidden")}>
          {renderInput({
            locale,
            value: current[locale] ?? "",
            onChange: (val) => handleChange(locale, val),
          })}
        </div>
      ))}
    </div>
  )
}

export { TranslatableField }
