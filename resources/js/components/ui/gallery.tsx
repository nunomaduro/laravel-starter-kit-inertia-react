import * as React from "react"
import { XIcon, ZoomInIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Dialog, DialogContent } from "@/components/ui/dialog"

export interface GalleryImage {
  id: string | number
  src: string
  alt?: string
  caption?: string
  width?: number
  height?: number
}

type GalleryLayout = "grid" | "masonry" | "columns"

interface GalleryProps {
  images: GalleryImage[]
  layout?: GalleryLayout
  columns?: 2 | 3 | 4 | 5
  gap?: "sm" | "md" | "lg"
  lightbox?: boolean
  className?: string
  renderImage?: (image: GalleryImage) => React.ReactNode
}

const gapMap = {
  sm: "gap-1",
  md: "gap-2",
  lg: "gap-4",
}

const columnsMap = {
  2: "grid-cols-2",
  3: "grid-cols-2 sm:grid-cols-3",
  4: "grid-cols-2 sm:grid-cols-3 lg:grid-cols-4",
  5: "grid-cols-2 sm:grid-cols-3 lg:grid-cols-5",
}

function Gallery({
  images,
  layout = "grid",
  columns = 3,
  gap = "md",
  lightbox = true,
  className,
  renderImage,
}: GalleryProps) {
  const [lightboxIndex, setLightboxIndex] = React.useState<number | null>(null)

  const handleImageClick = (index: number) => {
    if (lightbox) setLightboxIndex(index)
  }

  const handlePrev = () => {
    if (lightboxIndex === null) return
    setLightboxIndex((lightboxIndex - 1 + images.length) % images.length)
  }

  const handleNext = () => {
    if (lightboxIndex === null) return
    setLightboxIndex((lightboxIndex + 1) % images.length)
  }

  React.useEffect(() => {
    if (lightboxIndex === null) return
    const handler = (e: KeyboardEvent) => {
      if (e.key === "ArrowLeft") handlePrev()
      else if (e.key === "ArrowRight") handleNext()
    }
    window.addEventListener("keydown", handler)
    return () => window.removeEventListener("keydown", handler)
  }, [lightboxIndex])

  const containerClass =
    layout === "masonry"
      ? cn(`columns-${columns}`, gapMap[gap])
      : cn("grid", columnsMap[columns], gapMap[gap])

  return (
    <>
      <div
        data-slot="gallery"
        className={cn(containerClass, className)}
      >
        {images.map((image, index) => (
          <div
            key={image.id}
            data-slot="gallery-item"
            className={cn(
              "group relative overflow-hidden rounded-md",
              layout === "masonry" && "mb-2 break-inside-avoid"
            )}
            onClick={() => handleImageClick(index)}
          >
            {renderImage ? (
              renderImage(image)
            ) : (
              <>
                <img
                  src={image.src}
                  alt={image.alt ?? ""}
                  className={cn(
                    "w-full object-cover transition-transform duration-300",
                    lightbox && "cursor-pointer group-hover:scale-105"
                  )}
                />
                {lightbox && (
                  <div className="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition-all group-hover:bg-black/20 group-hover:opacity-100">
                    <ZoomInIcon className="size-8 text-white drop-shadow" />
                  </div>
                )}
                {image.caption && (
                  <p className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 px-3 py-2 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                    {image.caption}
                  </p>
                )}
              </>
            )}
          </div>
        ))}
      </div>

      {lightbox && lightboxIndex !== null && (
        <Dialog open onOpenChange={() => setLightboxIndex(null)}>
          <DialogContent className="max-w-5xl border-0 bg-black/95 p-0">
            <button
              type="button"
              onClick={() => setLightboxIndex(null)}
              className="absolute right-4 top-4 z-10 rounded-full bg-black/50 p-1.5 text-white hover:bg-black/80"
            >
              <XIcon className="size-4" />
            </button>
            <div className="flex items-center">
              <button
                type="button"
                onClick={handlePrev}
                className="px-4 py-8 text-white/60 hover:text-white"
                aria-label="Previous image"
              >
                ‹
              </button>
              <div className="flex-1">
                <img
                  src={images[lightboxIndex]!.src}
                  alt={images[lightboxIndex]!.alt ?? ""}
                  className="max-h-[80vh] w-full object-contain"
                />
                {images[lightboxIndex]!.caption && (
                  <p className="py-2 text-center text-sm text-white/70">
                    {images[lightboxIndex]!.caption}
                  </p>
                )}
              </div>
              <button
                type="button"
                onClick={handleNext}
                className="px-4 py-8 text-white/60 hover:text-white"
                aria-label="Next image"
              >
                ›
              </button>
            </div>
            <p className="pb-4 text-center text-xs text-white/40">
              {lightboxIndex + 1} / {images.length}
            </p>
          </DialogContent>
        </Dialog>
      )}
    </>
  )
}

export { Gallery }
