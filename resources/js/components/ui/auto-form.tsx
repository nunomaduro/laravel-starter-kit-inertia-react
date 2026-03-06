import * as React from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import {
  useForm,
  type DefaultValues,
  type FieldValues,
  type UseFormReturn,
} from "react-hook-form"
import { type z, type ZodObject, type ZodRawShape } from "zod"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

type FieldConfig = {
  label?: string
  description?: string
  inputProps?: React.InputHTMLAttributes<HTMLInputElement>
  component?: React.ComponentType<{
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    field: { value: any; onChange: (v: any) => void }
    label?: string
  }>
}

type AnyZodObject = ZodObject<ZodRawShape>

interface AutoFormProps<TSchema extends AnyZodObject> {
  schema: TSchema
  onSubmit: (values: z.infer<TSchema>) => void
  defaultValues?: DefaultValues<z.infer<TSchema>>
  fieldConfig?: Partial<Record<keyof z.infer<TSchema>, FieldConfig>>
  submitLabel?: string
  className?: string
  children?: (form: UseFormReturn<z.infer<TSchema>>) => React.ReactNode
}

function AutoForm<TSchema extends AnyZodObject>({
  schema,
  onSubmit,
  defaultValues,
  fieldConfig,
  submitLabel = "Submit",
  className,
  children,
}: AutoFormProps<TSchema>) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const form = useForm<any>({
    resolver: zodResolver(schema),
    defaultValues: defaultValues as DefaultValues<FieldValues>,
  })

  const shape = schema.shape

  return (
    <form
      data-slot="auto-form"
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      onSubmit={form.handleSubmit(onSubmit as (data: any) => void)}
      className={cn("space-y-4", className)}
    >
      {Object.keys(shape).map((key) => {
        const config = (fieldConfig as Record<string, FieldConfig> | undefined)?.[key]
        const label =
          config?.label ??
          key
            .replace(/([A-Z])/g, " $1")
            .replace(/^./, (s) => s.toUpperCase())
        const error = form.formState.errors[key]

        if (config?.component) {
          const CustomComponent = config.component
          return (
            <div key={key} className="space-y-1.5">
              <Label htmlFor={key}>{label}</Label>
              <CustomComponent
                field={{
                  value: form.watch(key),
                  // eslint-disable-next-line @typescript-eslint/no-explicit-any
                  onChange: (v: any) => form.setValue(key, v),
                }}
                label={label}
              />
              {error && (
                <p className="text-xs text-destructive">{String(error.message)}</p>
              )}
            </div>
          )
        }

        return (
          <div key={key} className="space-y-1.5">
            <Label htmlFor={key}>{label}</Label>
            <Input
              id={key}
              {...form.register(key)}
              {...config?.inputProps}
            />
            {config?.description && (
              <p className="text-xs text-muted-foreground">{config.description}</p>
            )}
            {error && (
              <p className="text-xs text-destructive">{String(error.message)}</p>
            )}
          </div>
        )
      })}
      {children?.(form as UseFormReturn<z.infer<TSchema>>)}
      <Button type="submit" disabled={form.formState.isSubmitting}>
        {submitLabel}
      </Button>
    </form>
  )
}

export { AutoForm }
