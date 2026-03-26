# React + Vite Best Practices v2.0.0

Performance optimization guide for React applications built with Vite.

## Overview

- Build optimization (manual chunks, minification with OXC, tree shaking, compression)
- Code splitting (React.lazy, Suspense, dynamic imports, prefetch hints)
- Development performance (dependency prebundling, HMR, Fast Refresh)
- Asset handling (images, SVGs, fonts, public directory)
- Environment configuration (VITE_ prefix, modes, secrets)
- Bundle analysis (rollup-plugin-visualizer)
- 23 rules across 6 categories

## Categories

### 1. Build Optimization (Critical)
Manual chunks, minification, build targets, sourcemaps, tree shaking, compression, asset hashing.

### 2. Code Splitting (Critical)
Route-based lazy loading, Suspense boundaries, dynamic imports, component lazy loading, prefetch hints.

### 3. Development (High)
Dependency prebundling, React Fast Refresh, HMR configuration.

### 4. Asset Handling (High)
Image optimization, SVG components, font loading, public directory usage.

### 5. Environment Config (Medium)
VITE_ prefix, mode-specific env files, sensitive data protection.

### 6. Bundle Analysis (Medium)
Bundle visualization with rollup-plugin-visualizer.

## Usage

```
Optimize this Vite build config
Review code splitting in my React app
Set up lazy loading for routes
Analyze my bundle size
```

## References

- [Vite Documentation](https://vite.dev)
- [React Documentation](https://react.dev)
- [Rollup Documentation](https://rollupjs.org)
