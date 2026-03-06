import * as React from "react"
import { ChevronDownIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Input } from "@/components/ui/input"

export interface CountryCode {
  code: string
  dialCode: string
  flag: string
  name: string
}

const COMMON_COUNTRIES: CountryCode[] = [
  { code: "US", dialCode: "+1", flag: "🇺🇸", name: "United States" },
  { code: "GB", dialCode: "+44", flag: "🇬🇧", name: "United Kingdom" },
  { code: "CA", dialCode: "+1", flag: "🇨🇦", name: "Canada" },
  { code: "AU", dialCode: "+61", flag: "🇦🇺", name: "Australia" },
  { code: "DE", dialCode: "+49", flag: "🇩🇪", name: "Germany" },
  { code: "FR", dialCode: "+33", flag: "🇫🇷", name: "France" },
  { code: "JP", dialCode: "+81", flag: "🇯🇵", name: "Japan" },
  { code: "IN", dialCode: "+91", flag: "🇮🇳", name: "India" },
  { code: "BR", dialCode: "+55", flag: "🇧🇷", name: "Brazil" },
  { code: "MX", dialCode: "+52", flag: "🇲🇽", name: "Mexico" },
]

interface PhoneInputProps
  extends Omit<React.ComponentProps<"input">, "onChange" | "value"> {
  value?: string
  onChange?: (value: string) => void
  defaultCountry?: string
  countries?: CountryCode[]
}

function PhoneInput({
  value = "",
  onChange,
  defaultCountry = "US",
  countries = COMMON_COUNTRIES,
  className,
  ...props
}: PhoneInputProps) {
  const [selectedCountry, setSelectedCountry] = React.useState<CountryCode>(
    () =>
      countries.find((c) => c.code === defaultCountry) ?? countries[0]!
  )
  const [phone, setPhone] = React.useState(value)

  React.useEffect(() => {
    setPhone(value)
  }, [value])

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newPhone = e.target.value
    setPhone(newPhone)
    onChange?.(selectedCountry.dialCode + newPhone)
  }

  const handleCountryChange = (country: CountryCode) => {
    setSelectedCountry(country)
    onChange?.(country.dialCode + phone)
  }

  return (
    <div data-slot="phone-input" className={cn("flex", className)}>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button
            variant="outline"
            className="h-9 shrink-0 rounded-r-none border-r-0 px-3 font-normal"
            type="button"
          >
            <span>{selectedCountry.flag}</span>
            <span className="text-xs text-muted-foreground">
              {selectedCountry.dialCode}
            </span>
            <ChevronDownIcon className="size-3 opacity-50" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="start" className="max-h-60 overflow-y-auto">
          {countries.map((country) => (
            <DropdownMenuItem
              key={country.code}
              onSelect={() => handleCountryChange(country)}
              className="gap-2"
            >
              <span>{country.flag}</span>
              <span>{country.name}</span>
              <span className="ml-auto text-xs text-muted-foreground">
                {country.dialCode}
              </span>
            </DropdownMenuItem>
          ))}
        </DropdownMenuContent>
      </DropdownMenu>
      <Input
        type="tel"
        value={phone}
        onChange={handlePhoneChange}
        className="rounded-l-none"
        placeholder="Phone number"
        {...props}
      />
    </div>
  )
}

export { PhoneInput, COMMON_COUNTRIES }
