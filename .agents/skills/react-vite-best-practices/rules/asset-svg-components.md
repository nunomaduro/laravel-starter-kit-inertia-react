---
title: Use SVGs as React Components
impact: HIGH
impactDescription: "Better styling and integration"
tags: asset, svg, components, react, vite
---

## Use SVGs as React Components

**Impact: HIGH (Better styling and integration)**

SVGs can be used as images or as React components. Using them as components enables styling with CSS, dynamic colors, and better integration with React.

## Incorrect

```typescript
// ❌ Bad: Using SVG as image - limited styling options
function Logo() {
  return <img src="/logo.svg" alt="Logo" className="w-8 h-8" />
}

// ❌ Bad: Inline SVG everywhere - duplicated code
function Icon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
      <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
    </svg>
  )
}
```

**Problems:**
- SVGs as `<img>` tags cannot be styled with CSS (no color changes, no hover effects)
- Inline SVGs are duplicated across components, bloating the bundle
- No tree shaking — unused icons still included in the build
- Cannot leverage `currentColor` for dynamic theming

## Correct

```bash
npm install vite-plugin-svgr -D
```

```typescript
// ✅ Good: vite.config.ts - Configure SVGR plugin
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import svgr from 'vite-plugin-svgr'

export default defineConfig({
  plugins: [
    react(),
    svgr({
      exportAsDefault: false,
      svgrOptions: {
        plugins: ['@svgr/plugin-svgo', '@svgr/plugin-jsx'],
        svgoConfig: {
          plugins: [
            {
              name: 'removeViewBox',
              active: false, // Keep viewBox for scaling
            },
          ],
        },
      },
    }),
  ],
})
```

```typescript
// ✅ Good: Import as React component for full styling control
import Logo from './assets/logo.svg?react'
import logoUrl from './assets/logo.svg'

function Header() {
  return (
    <header>
      {/* As component - fully styleable */}
      <Logo className="w-8 h-8 text-blue-600 hover:text-blue-700" />

      {/* As image when styling isn't needed */}
      <img src={logoUrl} alt="Logo" className="w-8 h-8" />
    </header>
  )
}
```

```typescript
// ✅ Good: TypeScript support
// src/vite-env.d.ts
/// <reference types="vite/client" />
/// <reference types="vite-plugin-svgr/client" />

declare module '*.svg?react' {
  import type { FunctionComponent, SVGProps } from 'react'
  const content: FunctionComponent<SVGProps<SVGSVGElement>>
  export default content
}

declare module '*.svg' {
  const content: string
  export default content
}
```

```typescript
// ✅ Good: Dynamic SVG colors via currentColor
import SearchIcon from './assets/search.svg?react'

function SearchButton({ active }: { active: boolean }) {
  return (
    <button className={active ? 'text-blue-600' : 'text-gray-400'}>
      <SearchIcon className="w-5 h-5" />
      Search
    </button>
  )
}
```

```typescript
// ✅ Good: Icon component pattern with tree shaking
import type { SVGProps, FunctionComponent } from 'react'

import HomeIcon from '@/assets/icons/home.svg?react'
import SettingsIcon from '@/assets/icons/settings.svg?react'
import UserIcon from '@/assets/icons/user.svg?react'

const icons = {
  home: HomeIcon,
  settings: SettingsIcon,
  user: UserIcon,
} as const

type IconName = keyof typeof icons

interface IconProps extends SVGProps<SVGSVGElement> {
  name: IconName
  size?: number
}

export function Icon({ name, size = 24, className, ...props }: IconProps) {
  const IconComponent = icons[name]
  return (
    <IconComponent
      width={size}
      height={size}
      className={className}
      {...props}
    />
  )
}

// Usage
// <Icon name="home" size={20} className="text-gray-600" />
```

**Benefits:**
- SVGs fully styleable with Tailwind CSS or any CSS framework
- Dynamic colors via `currentColor` without maintaining multiple SVG files
- Better tree shaking — unused icons excluded from the build
- SVGO optimization strips unnecessary metadata, reducing file size
- TypeScript support provides autocompletion for icon names

Reference: [vite-plugin-svgr](https://github.com/pd4d10/vite-plugin-svgr) | [SVGR](https://react-svgr.com/)
