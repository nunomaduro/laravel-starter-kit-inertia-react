import * as React from "react"
import { useDropzone, type Accept } from "react-dropzone"
import { FileIcon, FileTextIcon, ImageIcon, TrashIcon, UploadCloudIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Progress } from "@/components/ui/progress"

export interface UploadedFile {
  id: string
  file: File
  preview?: string
  progress?: number
  error?: string
}

interface FileUploaderProps {
  value?: UploadedFile[]
  onChange?: (files: UploadedFile[]) => void
  accept?: Accept
  maxSize?: number
  maxFiles?: number
  multiple?: boolean
  className?: string
  disabled?: boolean
}

function FileUploader({
  value = [],
  onChange,
  accept,
  maxSize = 10 * 1024 * 1024,
  maxFiles,
  multiple = true,
  className,
  disabled,
}: FileUploaderProps) {
  const [error, setError] = React.useState<string | null>(null)

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    accept,
    maxSize,
    multiple,
    maxFiles,
    disabled,
    onDropAccepted: (files) => {
      setError(null)
      const newFiles: UploadedFile[] = files.map((file) => {
        const id = `${file.name}-${Date.now()}-${Math.random().toString(36).slice(2)}`
        const preview = file.type.startsWith("image/")
          ? URL.createObjectURL(file)
          : undefined
        return { id, file, preview, progress: 0 }
      })
      onChange?.([...value, ...newFiles])
    },
    onDropRejected: (rejections) => {
      const msg = rejections[0]?.errors[0]?.message
      setError(msg ?? "File was rejected")
    },
  })

  const removeFile = (id: string) => {
    const file = value.find((f) => f.id === id)
    if (file?.preview) URL.revokeObjectURL(file.preview)
    onChange?.(value.filter((f) => f.id !== id))
  }

  React.useEffect(() => {
    return () => {
      value.forEach((f) => {
        if (f.preview) URL.revokeObjectURL(f.preview)
      })
    }
  }, [])

  return (
    <div data-slot="file-uploader" className={cn("flex flex-col gap-3", className)}>
      <div
        {...getRootProps()}
        className={cn(
          "flex flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed px-6 py-10 text-center transition-colors",
          isDragActive ? "border-primary bg-primary/5" : "border-border hover:border-primary/50",
          disabled && "cursor-not-allowed opacity-50",
          !disabled && "cursor-pointer hover:bg-muted/50"
        )}
      >
        <input {...getInputProps()} />
        <div className="flex size-12 items-center justify-center rounded-full bg-muted">
          <UploadCloudIcon className="size-6 text-muted-foreground" />
        </div>
        <div>
          <p className="text-sm font-medium">
            {isDragActive ? "Drop files here" : "Click or drag files here"}
          </p>
          <p className="text-xs text-muted-foreground">
            {maxFiles ? `Up to ${maxFiles} files` : "Multiple files accepted"}
            {" — "}max {Math.round(maxSize / 1024 / 1024)}MB each
          </p>
        </div>
      </div>
      {error && <p className="text-xs text-destructive">{error}</p>}
      {value.length > 0 && (
        <ul className="space-y-2">
          {value.map((uploadedFile) => (
            <FileItem
              key={uploadedFile.id}
              uploadedFile={uploadedFile}
              onRemove={() => removeFile(uploadedFile.id)}
              disabled={disabled}
            />
          ))}
        </ul>
      )}
    </div>
  )
}

function FileItem({
  uploadedFile,
  onRemove,
  disabled,
}: {
  uploadedFile: UploadedFile
  onRemove: () => void
  disabled?: boolean
}) {
  const { file, preview, progress, error } = uploadedFile
  const isImage = file.type.startsWith("image/")
  const isText = file.type.startsWith("text/") || file.name.endsWith(".pdf")

  return (
    <li className="flex items-center gap-3 rounded-md border bg-background p-2">
      <div className="flex size-10 shrink-0 items-center justify-center rounded-md bg-muted">
        {preview ? (
          <img
            src={preview}
            alt={file.name}
            className="size-full rounded-md object-cover"
          />
        ) : isImage ? (
          <ImageIcon className="size-5 text-muted-foreground" />
        ) : isText ? (
          <FileTextIcon className="size-5 text-muted-foreground" />
        ) : (
          <FileIcon className="size-5 text-muted-foreground" />
        )}
      </div>
      <div className="min-w-0 flex-1">
        <p className="truncate text-sm font-medium">{file.name}</p>
        <p className="text-xs text-muted-foreground">
          {(file.size / 1024).toFixed(1)} KB
        </p>
        {progress !== undefined && progress < 100 && (
          <Progress value={progress} className="mt-1 h-1" />
        )}
        {error && <p className="text-xs text-destructive">{error}</p>}
      </div>
      {!disabled && (
        <Button
          variant="ghost"
          size="icon-sm"
          onClick={onRemove}
          className="shrink-0 text-muted-foreground hover:text-destructive"
        >
          <TrashIcon className="size-4" />
        </Button>
      )}
    </li>
  )
}

export { FileUploader }
