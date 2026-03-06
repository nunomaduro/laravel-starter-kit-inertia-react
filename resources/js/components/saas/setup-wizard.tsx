import * as React from "react"
import { router } from "@inertiajs/react"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Progress } from "@/components/ui/progress"
import { Stepper } from "@/components/ui/stepper"
import { cn } from "@/lib/utils"

interface SetupWizardStep {
  id: string
  title: string
  description?: string
  content: React.ReactNode
}

interface SetupWizardProps {
  steps: SetupWizardStep[]
  title?: string
  description?: string
  /** URL param name to track current step (defaults to "step") */
  stepParam?: string
  initialStep?: number
  onComplete?: () => void
  className?: string
}

function SetupWizard({
  steps,
  title = "Setup",
  description,
  stepParam = "step",
  initialStep = 0,
  onComplete,
  className,
}: SetupWizardProps) {
  const [currentStep, setCurrentStep] = React.useState(() => {
    if (typeof window !== "undefined") {
      const params = new URLSearchParams(window.location.search)
      const val = parseInt(params.get(stepParam) ?? "", 10)
      if (!isNaN(val) && val >= 0 && val < steps.length) return val
    }
    return initialStep
  })

  const total = steps.length
  const percentage = total > 0 ? Math.round(((currentStep + 1) / total) * 100) : 0
  const isFirst = currentStep === 0
  const isLast = currentStep === total - 1
  const step = steps[currentStep]

  const navigateTo = (index: number) => {
    setCurrentStep(index)
    const url = new URL(window.location.href)
    url.searchParams.set(stepParam, String(index))
    router.visit(url.toString(), { replace: true, preserveState: true, preserveScroll: true })
  }

  const handleNext = () => {
    if (isLast) {
      onComplete?.()
    } else {
      navigateTo(currentStep + 1)
    }
  }

  const handleBack = () => {
    if (!isFirst) navigateTo(currentStep - 1)
  }

  const stepperSteps = steps.map((s) => ({ id: s.id, title: s.title, description: s.description }))

  return (
    <div className={cn("mx-auto max-w-2xl space-y-6 p-6", className)}>
      <div className="space-y-1">
        <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
        {description && <p className="text-muted-foreground">{description}</p>}
      </div>

      <div className="space-y-2">
        <div className="flex justify-between text-xs text-muted-foreground">
          <span>
            Step {currentStep + 1} of {total}
          </span>
          <span>{percentage}% complete</span>
        </div>
        <Progress value={percentage} className="h-1.5" />
      </div>

      <Stepper
        steps={stepperSteps}
        currentStep={currentStep}
        orientation="horizontal"
        onStepClick={navigateTo}
      />

      <Card>
        <CardHeader>
          <CardTitle>{step.title}</CardTitle>
          {step.description && (
            <CardDescription>{step.description}</CardDescription>
          )}
        </CardHeader>
        <CardContent>{step.content}</CardContent>
        <CardFooter className="flex justify-between">
          <Button variant="outline" onClick={handleBack} disabled={isFirst}>
            Back
          </Button>
          <Button onClick={handleNext}>
            {isLast ? "Finish" : "Next"}
          </Button>
        </CardFooter>
      </Card>
    </div>
  )
}

export { SetupWizard }
export type { SetupWizardStep, SetupWizardProps }
