import * as React from "react"
import { ChevronLeftIcon, ChevronRightIcon, XIcon, ZoomInIcon, ZoomOutIcon } from "lucide-react"
import { Dialog as DialogPrimitive } from "radix-ui"

import { cn } from "@/lib/utils"

export interface LightboxImage {
  src: string
  alt?: string
  caption?: string
}

interface LightboxProps {
  images: LightboxImage[]
  initialIndex?: number
  open: boolean
  onOpenChange: (open: boolean) => void
  className?: string
}

function Lightbox({
  images,
  initialIndex = 0,
  open,
  onOpenChange,
  className,
}: LightboxProps) {
  const [currentIndex, setCurrentIndex] = React.useState(initialIndex)
  const [zoom, setZoom] = React.useState(1)

  React.useEffect(() => {
    if (open) {
      setCurrentIndex(initialIndex)
      setZoom(1)
    }
  }, [open, initialIndex])

  const prev = React.useCallback(() => {
    setCurrentIndex((i) => (i - 1 + images.length) % images.length)
    setZoom(1)
  }, [images.length])

  const next = React.useCallback(() => {
    setCurrentIndex((i) => (i + 1) % images.length)
    setZoom(1)
  }, [images.length])

  const zoomIn = () => setZoom((z) => Math.min(z + 0.5, 4))
  const zoomOut = () => setZoom((z) => Math.max(z - 0.5, 0.5))

  React.useEffect(() => {
    if (!open) {
      return
    }

    const handleKey = (e: KeyboardEvent) => {
      if (e.key === "ArrowLeft") {
        prev()
      } else if (e.key === "ArrowRight") {
        next()
      } else if (e.key === "+" || e.key === "=") {
        zoomIn()
      } else if (e.key === "-") {
        zoomOut()
      }
    }

    window.addEventListener("keydown", handleKey)

    return () => window.removeEventListener("keydown", handleKey)
  }, [open, prev, next])

  const current = images[currentIndex]

  if (!current) {
    return null
  }

  return (
    <DialogPrimitive.Root open={open} onOpenChange={onOpenChange}>
      <DialogPrimitive.Portal>
        <DialogPrimitive.Overlay className="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-50 bg-black/90" />
        <DialogPrimitive.Content
          data-slot="lightbox"
          className={cn(
            "fixed inset-0 z-50 flex flex-col items-center justify-center outline-none",
            className
          )}
        >
          <DialogPrimitive.Title className="sr-only">
            {current.alt ?? "Lightbox image"}
          </DialogPrimitive.Title>

          {/* Close button */}
          <DialogPrimitive.Close className="absolute top-4 right-4 z-10 flex size-9 items-center justify-center rounded-full bg-black/40 text-white opacity-80 hover:opacity-100 transition-opacity">
            <XIcon className="size-5" />
            <span className="sr-only">Close</span>
          </DialogPrimitive.Close>

          {/* Zoom controls */}
          <div className="absolute top-4 left-4 z-10 flex gap-2">
            <button
              type="button"
              onClick={zoomOut}
              className="flex size-9 items-center justify-center rounded-full bg-black/40 text-white opacity-80 hover:opacity-100 transition-opacity"
              aria-label="Zoom out"
            >
              <ZoomOutIcon className="size-4" />
            </button>
            <button
              type="button"
              onClick={zoomIn}
              className="flex size-9 items-center justify-center rounded-full bg-black/40 text-white opacity-80 hover:opacity-100 transition-opacity"
              aria-label="Zoom in"
            >
              <ZoomInIcon className="size-4" />
            </button>
          </div>

          {/* Previous button */}
          {images.length > 1 && (
            <button
              type="button"
              onClick={prev}
              className="absolute left-4 top-1/2 z-10 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-black/40 text-white opacity-80 hover:opacity-100 transition-opacity"
              aria-label="Previous image"
            >
              <ChevronLeftIcon className="size-6" />
            </button>
          )}

          {/* Image */}
          <div className="flex max-h-[85vh] max-w-[90vw] items-center justify-center overflow-auto">
            <img
              src={current.src}
              alt={current.alt ?? ""}
              className="object-contain transition-transform duration-200"
              style={{ transform: `scale(${zoom})`, transformOrigin: "center center" }}
              draggable={false}
            />
          </div>

          {/* Next button */}
          {images.length > 1 && (
            <button
              type="button"
              onClick={next}
              className="absolute right-4 top-1/2 z-10 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-black/40 text-white opacity-80 hover:opacity-100 transition-opacity"
              aria-label="Next image"
            >
              <ChevronRightIcon className="size-6" />
            </button>
          )}

          {/* Caption and counter */}
          <div className="absolute bottom-4 left-0 right-0 flex flex-col items-center gap-1">
            {current.caption && (
              <p className="text-white/90 text-sm text-center px-4">{current.caption}</p>
            )}
            {images.length > 1 && (
              <p className="text-white/60 text-xs">
                {currentIndex + 1} / {images.length}
              </p>
            )}
          </div>
        </DialogPrimitive.Content>
      </DialogPrimitive.Portal>
    </DialogPrimitive.Root>
  )
}

export { Lightbox }
