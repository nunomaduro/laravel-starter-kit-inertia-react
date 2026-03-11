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
import { FormField } from "@/components/ui/form-field"
import { Input } from "@/components/ui/input"

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
  /** Server-side validation errors (e.g. from Inertia page.props.errors). Applied to form state when present. */
  serverErrors?: Record<string, string>
}

function AutoForm<TSchema extends AnyZodObject>({
  schema,
  onSubmit,
  defaultValues,
  fieldConfig,
  submitLabel = "Submit",
  className,
  children,
  serverErrors,
}: AutoFormProps<TSchema>) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const form = useForm<any>({
    resolver: zodResolver(schema),
    defaultValues: defaultValues as DefaultValues<FieldValues>,
  })

  React.useEffect(() => {
    if (serverErrors && Object.keys(serverErrors).length > 0) {
      Object.entries(serverErrors).forEach(([key, message]) => {
        form.setError(key, { type: "server", message })
      })
    }
  }, [serverErrors, form])

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
        const errorMessage = error?.message ? String(error.message) : undefined

        if (config?.component) {
          const CustomComponent = config.component
          return (
            <FormField key={key} label={label} htmlFor={key} error={errorMessage}>
              <CustomComponent
                field={{
                  value: form.watch(key),
                  // eslint-disable-next-line @typescript-eslint/no-explicit-any
                  onChange: (v: any) => form.setValue(key, v),
                }}
                label={label}
              />
            </FormField>
          )
        }

        return (
          <FormField
            key={key}
            label={label}
            htmlFor={key}
            description={config?.description}
            error={errorMessage}
          >
            <Input
              id={key}
              {...form.register(key)}
              {...config?.inputProps}
            />
          </FormField>
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
