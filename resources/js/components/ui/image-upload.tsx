import * as React from "react"
import { useDropzone } from "react-dropzone"
import { ImageIcon, TrashIcon, UploadIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

interface ImageUploadProps {
  value?: string | null
  onChange?: (value: string | null) => void
  onFileChange?: (file: File | null) => void
  accept?: string[]
  maxSize?: number
  aspectRatio?: number
  className?: string
  disabled?: boolean
  placeholder?: string
}

function ImageUpload({
  value,
  onChange,
  onFileChange,
  accept = ["image/jpeg", "image/png", "image/webp", "image/gif"],
  maxSize = 4 * 1024 * 1024,
  className,
  disabled,
  placeholder = "Click or drag to upload an image",
}: ImageUploadProps) {
  const [preview, setPreview] = React.useState<string | null>(value ?? null)
  const [error, setError] = React.useState<string | null>(null)

  React.useEffect(() => {
    setPreview(value ?? null)
  }, [value])

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    accept: accept.reduce((acc, type) => ({ ...acc, [type]: [] }), {}),
    maxSize,
    disabled,
    multiple: false,
    onDropAccepted: (files) => {
      const file = files[0]
      if (!file) return
      setError(null)
      const reader = new FileReader()
      reader.onloadend = () => {
        const dataUrl = reader.result as string
        setPreview(dataUrl)
        onChange?.(dataUrl)
        onFileChange?.(file)
      }
      reader.readAsDataURL(file)
    },
    onDropRejected: (fileRejections) => {
      const rejection = fileRejections[0]
      if (rejection?.errors[0]) {
        setError(rejection.errors[0].message)
      }
    },
  })

  const handleRemove = (e: React.MouseEvent) => {
    e.stopPropagation()
    setPreview(null)
    onChange?.(null)
    onFileChange?.(null)
  }

  return (
    <div data-slot="image-upload" className={cn("group", className)}>
      <div
        {...getRootProps()}
        className={cn(
          "relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed transition-colors",
          isDragActive
            ? "border-primary bg-primary/5"
            : "border-border hover:border-primary/50 hover:bg-muted/50",
          disabled && "cursor-not-allowed opacity-50",
          !disabled && "cursor-pointer"
        )}
        style={{ minHeight: 160 }}
      >
        <input {...getInputProps()} />
        {preview ? (
          <>
            <img
              src={preview}
              alt="Upload preview"
              className="h-full w-full rounded-md object-contain p-2"
              style={{ maxHeight: 240 }}
            />
            {!disabled && (
              <Button
                variant="destructive"
                size="icon-sm"
                className="absolute right-2 top-2 opacity-0 transition-opacity group-hover:opacity-100"
                onClick={handleRemove}
              >
                <TrashIcon className="size-3.5" />
              </Button>
            )}
          </>
        ) : (
          <div className="flex flex-col items-center gap-2 p-6 text-center">
            <div className="flex size-10 items-center justify-center rounded-full bg-muted">
              {isDragActive ? (
                <ImageIcon className="size-5 text-primary" />
              ) : (
                <UploadIcon className="size-5 text-muted-foreground" />
              )}
            </div>
            <div>
              <p className="text-sm font-medium">
                {isDragActive ? "Drop the image here" : placeholder}
              </p>
              <p className="text-xs text-muted-foreground">
                {accept.join(", ")} — max {Math.round(maxSize / 1024 / 1024)}MB
              </p>
            </div>
          </div>
        )}
      </div>
      {error && (
        <p className="mt-1.5 text-xs text-destructive">{error}</p>
      )}
    </div>
  )
}

export { ImageUpload }
