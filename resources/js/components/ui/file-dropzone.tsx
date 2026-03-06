import * as React from "react"
import { useDropzone, type DropzoneOptions } from "react-dropzone"
import { UploadCloudIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface FileDropzoneProps extends Omit<DropzoneOptions, "onDrop"> {
  onDrop?: (files: File[]) => void
  className?: string
  label?: string
  description?: string
  children?: React.ReactNode
}

function FileDropzone({
  onDrop,
  className,
  label = "Drag & drop files here",
  description,
  children,
  ...options
}: FileDropzoneProps) {
  const { getRootProps, getInputProps, isDragActive, isDragReject } = useDropzone({
    onDrop,
    ...options,
  })

  return (
    <div
      {...getRootProps()}
      data-slot="file-dropzone"
      className={cn(
        "border-input bg-background flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-8 text-center transition-colors",
        isDragActive && !isDragReject && "border-primary bg-primary/5",
        isDragReject && "border-destructive bg-destructive/5",
        "hover:border-primary/60 hover:bg-accent/40",
        "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
        className
      )}
    >
      <input {...getInputProps()} />
      {children ?? (
        <>
          <UploadCloudIcon
            className={cn(
              "size-10 text-muted-foreground",
              isDragActive && !isDragReject && "text-primary",
              isDragReject && "text-destructive"
            )}
          />
          <div>
            <p className="text-sm font-medium">
              {isDragReject
                ? "File type not accepted"
                : isDragActive
                  ? "Drop files here"
                  : label}
            </p>
            {description && !isDragActive && (
              <p className="text-muted-foreground mt-1 text-xs">{description}</p>
            )}
          </div>
        </>
      )}
    </div>
  )
}

export { FileDropzone }
