import * as React from "react"
import {
  ReactCompareSlider,
  ReactCompareSliderImage,
} from "react-compare-slider"

import { cn } from "@/lib/utils"

interface ImageComparisonProps {
  before: string | React.ReactNode
  after: string | React.ReactNode
  beforeLabel?: string
  afterLabel?: string
  orientation?: "horizontal" | "vertical"
  className?: string
  position?: number
}

function ImageComparison({
  before,
  after,
  beforeLabel = "Before",
  afterLabel = "After",
  orientation = "horizontal",
  className,
  position = 50,
}: ImageComparisonProps) {
  const renderSlot = (src: string | React.ReactNode, label: string) => {
    if (typeof src === "string") {
      return (
        <div className="relative">
          <ReactCompareSliderImage src={src} alt={label} />
          <span className="absolute bottom-2 left-2 rounded bg-black/60 px-2 py-0.5 text-xs font-medium text-white">
            {label}
          </span>
        </div>
      )
    }
    return (
      <div className="relative h-full w-full">
        {src}
        <span className="absolute bottom-2 left-2 rounded bg-black/60 px-2 py-0.5 text-xs font-medium text-white">
          {label}
        </span>
      </div>
    )
  }

  return (
    <div
      data-slot="image-comparison"
      className={cn("overflow-hidden rounded-lg border", className)}
    >
      <ReactCompareSlider
        itemOne={renderSlot(before, beforeLabel)}
        itemTwo={renderSlot(after, afterLabel)}
        portrait={orientation === "vertical"}
        position={position}
        style={{ width: "100%", height: "100%" }}
      />
    </div>
  )
}

export { ImageComparison }
