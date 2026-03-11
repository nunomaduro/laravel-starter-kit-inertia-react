import * as React from "react"
import ReactDiffViewer, { type ReactDiffViewerStylesOverride } from "react-diff-viewer-continued"

import { cn } from "@/lib/utils"

interface DiffViewerProps {
  oldValue: string
  newValue: string
  language?: string
  splitView?: boolean
  hideLineNumbers?: boolean
  showDiffOnly?: boolean
  leftTitle?: string
  rightTitle?: string
  className?: string
}

function DiffViewer({
  oldValue,
  newValue,
  splitView,
  hideLineNumbers = false,
  showDiffOnly = false,
  leftTitle,
  rightTitle,
  className,
}: DiffViewerProps) {

  const [isMobile, setIsMobile] = React.useState(false)
  const [isDark, setIsDark] = React.useState(false)

  React.useEffect(() => {
    const check = () => setIsMobile(window.innerWidth < 768)
    check()
    window.addEventListener("resize", check)
    return () => window.removeEventListener("resize", check)
  }, [])

  React.useEffect(() => {
    const el = document.documentElement
    const check = () => setIsDark(el.classList.contains("dark"))
    check()
    const obs = new MutationObserver(check)
    obs.observe(el, { attributes: true, attributeFilter: ["class"] })
    return () => obs.disconnect()
  }, [])

  const useSplit = splitView ?? !isMobile

  const styles: ReactDiffViewerStylesOverride = {
    variables: {
      dark: {
        diffViewerBackground: "transparent",
        addedBackground: "#1a3f1a",
        addedColor: "#98c379",
        removedBackground: "#3f1a1a",
        removedColor: "#e06c75",
        wordAddedBackground: "#264d1e",
        wordRemovedBackground: "#4d1e1e",
        codeFoldBackground: "#1e1e2e",
        emptyLineBackground: "#1e1e2e",
        gutterBackground: "#1e1e2e",
        gutterColor: "#4a4a6a",
      },
      light: {
        diffViewerBackground: "transparent",
        addedBackground: "#e6ffe6",
        addedColor: "#22863a",
        removedBackground: "#fff0f0",
        removedColor: "#cb2431",
        wordAddedBackground: "#c8f5c8",
        wordRemovedBackground: "#ffc8c8",
        codeFoldBackground: "#f6f8fa",
        emptyLineBackground: "#f6f8fa",
        gutterBackground: "#f6f8fa",
        gutterColor: "#6a737d",
      },
    },
  }

  return (
    <div
      data-slot="diff-viewer"
      className={cn(
        "overflow-auto rounded-lg border font-mono text-sm",
        className
      )}
    >
      <ReactDiffViewer
        oldValue={oldValue}
        newValue={newValue}
        splitView={useSplit}
        hideLineNumbers={hideLineNumbers}
        showDiffOnly={showDiffOnly}
        leftTitle={leftTitle}
        rightTitle={rightTitle}
        useDarkTheme={isDark}
        styles={styles}
      />
    </div>
  )
}

export { DiffViewer }
