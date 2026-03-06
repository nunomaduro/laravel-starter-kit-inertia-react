import * as React from "react"
import useEmblaCarousel, { type UseEmblaCarouselType } from "embla-carousel-react"
import { ArrowLeftIcon, ArrowRightIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

type CarouselCnApi = UseEmblaCarouselType[1]

interface CarouselCnProps {
  children: React.ReactNode
  opts?: Parameters<typeof useEmblaCarousel>[0]
  plugins?: Parameters<typeof useEmblaCarousel>[1]
  orientation?: "horizontal" | "vertical"
  setApi?: (api: CarouselCnApi) => void
  className?: string
  showArrows?: boolean
  showDots?: boolean
  loop?: boolean
}

function CarouselCn({
  children,
  opts,
  plugins,
  orientation = "horizontal",
  setApi,
  className,
  showArrows = true,
  showDots = false,
  loop = false,
}: CarouselCnProps) {
  const [emblaRef, emblaApi] = useEmblaCarousel(
    {
      axis: orientation === "horizontal" ? "x" : "y",
      loop,
      ...opts,
    },
    plugins
  )
  const [canScrollPrev, setCanScrollPrev] = React.useState(false)
  const [canScrollNext, setCanScrollNext] = React.useState(false)
  const [selectedIndex, setSelectedIndex] = React.useState(0)
  const [scrollSnaps, setScrollSnaps] = React.useState<number[]>([])

  const onSelect = React.useCallback((api: CarouselCnApi) => {
    if (!api) return
    setCanScrollPrev(api.canScrollPrev())
    setCanScrollNext(api.canScrollNext())
    setSelectedIndex(api.selectedScrollSnap())
  }, [])

  React.useEffect(() => {
    if (!emblaApi) return
    setScrollSnaps(emblaApi.scrollSnapList())
    onSelect(emblaApi)
    emblaApi.on("reInit", onSelect)
    emblaApi.on("select", onSelect)
    setApi?.(emblaApi)
    return () => {
      emblaApi.off("reInit", onSelect)
      emblaApi.off("select", onSelect)
    }
  }, [emblaApi, onSelect, setApi])

  return (
    <div
      data-slot="carouselcn"
      data-orientation={orientation}
      className={cn("relative", className)}
    >
      <div ref={emblaRef} className="overflow-hidden">
        <div
          className={cn(
            "flex",
            orientation === "horizontal" ? "flex-row" : "flex-col"
          )}
        >
          {children}
        </div>
      </div>
      {showArrows && (
        <>
          <Button
            variant="outline"
            size="icon"
            className={cn(
              "absolute rounded-full shadow-md",
              orientation === "horizontal"
                ? "-left-4 top-1/2 -translate-y-1/2"
                : "-top-4 left-1/2 -translate-x-1/2 rotate-90"
            )}
            onClick={() => emblaApi?.scrollPrev()}
            disabled={!canScrollPrev}
          >
            <ArrowLeftIcon className="size-4" />
            <span className="sr-only">Previous</span>
          </Button>
          <Button
            variant="outline"
            size="icon"
            className={cn(
              "absolute rounded-full shadow-md",
              orientation === "horizontal"
                ? "-right-4 top-1/2 -translate-y-1/2"
                : "-bottom-4 left-1/2 -translate-x-1/2 rotate-90"
            )}
            onClick={() => emblaApi?.scrollNext()}
            disabled={!canScrollNext}
          >
            <ArrowRightIcon className="size-4" />
            <span className="sr-only">Next</span>
          </Button>
        </>
      )}
      {showDots && scrollSnaps.length > 1 && (
        <div className="mt-4 flex justify-center gap-1.5">
          {scrollSnaps.map((_, index) => (
            <button
              key={index}
              type="button"
              className={cn(
                "size-2 rounded-full transition-colors",
                index === selectedIndex
                  ? "bg-primary"
                  : "bg-muted-foreground/30 hover:bg-muted-foreground/50"
              )}
              onClick={() => emblaApi?.scrollTo(index)}
              aria-label={`Go to slide ${index + 1}`}
            />
          ))}
        </div>
      )}
    </div>
  )
}

function CarouselCnItem({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="carouselcn-item"
      className={cn("min-w-0 shrink-0 grow-0 basis-full", className)}
      {...props}
    />
  )
}

export { CarouselCn, CarouselCnItem }
export type { CarouselCnApi }
