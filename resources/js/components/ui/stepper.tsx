import * as React from "react"
import { CheckIcon } from "lucide-react"

import { cn } from "@/lib/utils"

export interface StepperStep {
  id: string | number
  title: string
  description?: string
  icon?: React.ReactNode
  optional?: boolean
}

interface StepperProps {
  steps: StepperStep[]
  currentStep: number
  orientation?: "horizontal" | "vertical"
  className?: string
  onStepClick?: (index: number) => void
}

function Stepper({
  steps,
  currentStep,
  orientation = "horizontal",
  className,
  onStepClick,
}: StepperProps) {
  return (
    <div
      data-slot="stepper"
      className={cn(
        "flex",
        orientation === "horizontal"
          ? "flex-row items-start gap-0"
          : "flex-col gap-0",
        className
      )}
    >
      {steps.map((step, index) => {
        const isCompleted = index < currentStep
        const isCurrent = index === currentStep
        const isLast = index === steps.length - 1

        return (
          <React.Fragment key={step.id}>
            <StepperItem
              step={step}
              index={index}
              isCompleted={isCompleted}
              isCurrent={isCurrent}
              orientation={orientation}
              onClick={onStepClick ? () => onStepClick(index) : undefined}
            />
            {!isLast && (
              <StepperSeparator
                orientation={orientation}
                isCompleted={isCompleted}
              />
            )}
          </React.Fragment>
        )
      })}
    </div>
  )
}

interface StepperItemProps {
  step: StepperStep
  index: number
  isCompleted: boolean
  isCurrent: boolean
  orientation: "horizontal" | "vertical"
  onClick?: () => void
}

function StepperItem({
  step,
  index,
  isCompleted,
  isCurrent,
  orientation,
  onClick,
}: StepperItemProps) {
  return (
    <div
      data-slot="stepper-item"
      data-completed={isCompleted || undefined}
      data-current={isCurrent || undefined}
      onClick={onClick}
      className={cn(
        "flex gap-3",
        orientation === "horizontal" ? "flex-col items-center" : "flex-row",
        onClick && "cursor-pointer"
      )}
    >
      <div
        className={cn(
          "flex size-8 shrink-0 items-center justify-center rounded-full border-2 text-sm font-medium transition-colors",
          isCompleted &&
            "border-primary bg-primary text-primary-foreground",
          isCurrent &&
            "border-primary bg-background text-primary",
          !isCompleted &&
            !isCurrent &&
            "border-muted-foreground/30 bg-background text-muted-foreground"
        )}
      >
        {isCompleted ? <CheckIcon className="size-4" /> : (step.icon ?? index + 1)}
      </div>
      <div
        className={cn(
          "flex flex-col",
          orientation === "horizontal" ? "items-center text-center" : ""
        )}
      >
        <span
          className={cn(
            "text-sm font-medium",
            isCurrent ? "text-foreground" : "text-muted-foreground"
          )}
        >
          {step.title}
        </span>
        {step.description && (
          <span className="text-xs text-muted-foreground">
            {step.description}
          </span>
        )}
        {step.optional && (
          <span className="text-xs text-muted-foreground">(Optional)</span>
        )}
      </div>
    </div>
  )
}

function StepperSeparator({
  orientation,
  isCompleted,
}: {
  orientation: "horizontal" | "vertical"
  isCompleted: boolean
}) {
  return (
    <div
      data-slot="stepper-separator"
      className={cn(
        "transition-colors",
        orientation === "horizontal"
          ? "mt-4 h-px flex-1 min-w-8"
          : "ms-4 w-px flex-1 min-h-4",
        isCompleted ? "bg-primary" : "bg-border"
      )}
    />
  )
}

export { Stepper, StepperItem, StepperSeparator }
