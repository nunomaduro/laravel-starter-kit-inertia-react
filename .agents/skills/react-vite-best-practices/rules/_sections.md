# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Build Optimization (build)

**Impact:** CRITICAL
**Description:** Vite build configuration for production. Manual chunk splitting, minification (OXC default, Terser for max compression), modern browser targets, sourcemap configuration, tree shaking, gzip/Brotli compression, and content-based asset hashing.

## 2. Code Splitting (split)

**Impact:** CRITICAL
**Description:** Route-based and component-level code splitting with React.lazy() and Suspense. Dynamic imports for heavy libraries, strategic Suspense boundary placement, and prefetch hints for anticipated navigation.

## 3. Development (dev)

**Impact:** HIGH
**Description:** Development server performance. Dependency pre-bundling with optimizeDeps, React Fast Refresh patterns for reliable HMR, and server configuration for HMR overlay, Docker, and proxy setups.

## 4. Asset Handling (asset)

**Impact:** HIGH
**Description:** Static asset optimization. Image lazy loading and responsive formats, SVG-as-React-components with SVGR, self-hosted web fonts with preloading, and correct usage of the public directory vs JavaScript imports.

## 5. Environment Config (env)

**Impact:** MEDIUM
**Description:** Environment variable management. The VITE_ prefix for client-side exposure, mode-specific env files (.env.production, .env.staging), and protecting sensitive data from being embedded in the client bundle.

## 6. Bundle Analysis (bundle)

**Impact:** MEDIUM
**Description:** Bundle size analysis and monitoring. Using rollup-plugin-visualizer to identify large dependencies and optimization opportunities.
