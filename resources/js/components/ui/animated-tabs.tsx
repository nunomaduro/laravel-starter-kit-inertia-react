import * as React from "react"
import { Tabs as TabsPrimitive } from "radix-ui"

import { cn } from "@/lib/utils"

const AnimatedTabsContext = React.createContext<{
  activeTab: string
  indicatorStyle: React.CSSProperties
  setIndicator: (el: HTMLElement, value: string) => void
}>({
  activeTab: "",
  indicatorStyle: {},
  setIndicator: () => {},
})

interface AnimatedTabsProps extends React.ComponentProps<typeof TabsPrimitive.Root> {
  variant?: "underline" | "pill" | "card"
}

function AnimatedTabs({ variant = "pill", children, defaultValue, value, onValueChange, ...props }: AnimatedTabsProps) {
  const [activeTab, setActiveTab] = React.useState(value ?? defaultValue ?? "")
  const [indicatorStyle, setIndicatorStyle] = React.useState<React.CSSProperties>({})
  const listRef = React.useRef<HTMLDivElement>(null)

  const setIndicator = React.useCallback((el: HTMLElement, tabValue: string) => {
    if (tabValue !== activeTab) return
    const list = listRef.current
    if (!list) return
    const listRect = list.getBoundingClientRect()
    const elRect = el.getBoundingClientRect()
    setIndicatorStyle({
      left: elRect.left - listRect.left,
      width: elRect.width,
    })
  }, [activeTab])

  const handleValueChange = (newValue: string) => {
    setActiveTab(newValue)
    onValueChange?.(newValue)
  }

  return (
    <AnimatedTabsContext value={{ activeTab, indicatorStyle, setIndicator }}>
      <TabsPrimitive.Root
        data-slot="animated-tabs"
        data-variant={variant}
        value={activeTab}
        defaultValue={defaultValue}
        onValueChange={handleValueChange}
        {...props}
      >
        {children}
      </TabsPrimitive.Root>
    </AnimatedTabsContext>
  )
}

function AnimatedTabsList({
  className,
  ...props
}: React.ComponentProps<typeof TabsPrimitive.List>) {
  const { indicatorStyle } = React.use(AnimatedTabsContext)
  const ref = React.useRef<HTMLDivElement>(null)

  return (
    <div ref={ref} className="relative">
      <TabsPrimitive.List
        data-slot="animated-tabs-list"
        className={cn(
          "relative flex h-9 items-center gap-1 rounded-lg bg-muted p-1",
          className
        )}
        {...props}
      />
      <span
        className="absolute inset-y-1 rounded-md bg-background shadow transition-all duration-200 ease-in-out"
        style={indicatorStyle}
      />
    </div>
  )
}

function AnimatedTabsTrigger({
  className,
  value,
  ...props
}: React.ComponentProps<typeof TabsPrimitive.Trigger>) {
  const { setIndicator } = React.use(AnimatedTabsContext)
  const ref = React.useRef<HTMLButtonElement>(null)

  React.useLayoutEffect(() => {
    if (ref.current) setIndicator(ref.current, value)
  })

  return (
    <TabsPrimitive.Trigger
      ref={ref}
      data-slot="animated-tabs-trigger"
      value={value}
      className={cn(
        "relative z-10 inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium transition-colors",
        "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
        "disabled:pointer-events-none disabled:opacity-50",
        "data-[state=active]:text-foreground text-muted-foreground",
        className
      )}
      {...props}
    />
  )
}

function AnimatedTabsContent({
  className,
  ...props
}: React.ComponentProps<typeof TabsPrimitive.Content>) {
  return (
    <TabsPrimitive.Content
      data-slot="animated-tabs-content"
      className={cn(
        "mt-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
        className
      )}
      {...props}
    />
  )
}

export { AnimatedTabs, AnimatedTabsList, AnimatedTabsTrigger, AnimatedTabsContent }
