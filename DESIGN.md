# Design System — Laravel Starter Kit

## Product Context
- **What this is:** Multi-tenant Laravel SaaS starter kit with CRM, blog, AI chat, and admin dashboard
- **Who it's for:** Developers and teams building SaaS products — mixed audience including technical and non-technical end users
- **Space/industry:** Developer tools, SaaS infrastructure, starter kits
- **Project type:** Web application (dashboard + admin + marketing/landing pages)

## Aesthetic Direction
- **Direction:** Industrial-Minimal — function-first with sharp edges and restrained decoration
- **Decoration level:** Minimal — typography and spacing do the work. Subtle 1px borders for structure. No gradients, no shadows on cards (use background differentiation instead)
- **Mood:** Precise, competent, developer-native. Every pixel communicates utility. The interface should feel like a well-configured terminal — fast, predictable, and satisfying to use
- **Reference sites:** Linear (app.linear.app), Raycast (raycast.com), Vercel (vercel.com)
- **Anti-references:** Generic Bootstrap/Material, cluttered dashboards, glassmorphism, oversized rounded corners, decorative gradients

## Typography
- **Display/Hero:** JetBrains Mono at 700 weight — monospace headings give an unmistakably developer-built identity. Use for h1-h4 headings, stat values, and hero text
- **Body:** IBM Plex Sans at 400/500/600 — clean, technical, designed for data-dense interfaces. Excellent legibility at small sizes. Use for paragraphs, descriptions, labels, and UI text
- **UI/Labels:** IBM Plex Sans at 500-600, uppercase with letter-spacing for section labels and overlines
- **Data/Tables:** JetBrains Mono — tabular numerals built-in, perfect for metrics, timestamps, and data cells
- **Code:** JetBrains Mono at 400 — same family as display, unified developer identity across headings and code
- **Loading:** Google Fonts — `family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600;700`
- **Scale:**
  - 40px / JetBrains Mono 700 — Hero / Page title (letter-spacing: -0.03em)
  - 32px / JetBrains Mono 700 — Section heading (letter-spacing: -0.025em)
  - 24px / JetBrains Mono 600 — Subsection heading (letter-spacing: -0.02em)
  - 20px / JetBrains Mono 600 — Card heading (letter-spacing: -0.015em)
  - 16px / IBM Plex Sans 400 — Body text (line-height: 1.6)
  - 14px / IBM Plex Sans 400 — Secondary text (line-height: 1.5)
  - 12px / IBM Plex Sans 600 — Labels, captions, overlines (uppercase, letter-spacing: 0.04em)
  - 11px / JetBrains Mono 500 — Section labels, badges (uppercase, letter-spacing: 0.06em)

## Color

### Approach
Restrained — one accent color plus neutrals. Color is rare and meaningful. Cool blue undertone in all neutrals adds depth and sophistication without being noticeable.

### Dark Mode (Primary)
| Token | Value | Usage |
|-------|-------|-------|
| `--background` | `oklch(0.11 0.005 260)` | Page background, base layer |
| `--surface` | `oklch(0.15 0.005 260)` | Cards, sidebar, elevated panels |
| `--surface-hover` | `oklch(0.18 0.005 260)` | Hover state for surface elements |
| `--border` | `oklch(0.22 0.005 260)` | Borders, dividers, structure |
| `--border-subtle` | `oklch(0.18 0.005 260)` | Table row dividers, subtle separators |
| `--text` | `oklch(0.93 0 0)` | Primary text (off-white, not pure white) |
| `--text-secondary` | `oklch(0.7 0 0)` | Descriptions, metadata |
| `--text-muted` | `oklch(0.55 0 0)` | Placeholders, disabled text, hints |
| `--accent` | `oklch(0.65 0.14 165)` | Primary accent — muted teal. Links, active states, highlights |
| `--accent-hover` | `oklch(0.72 0.14 165)` | Accent hover state |
| `--accent-muted` | `oklch(0.65 0.14 165 / 0.15)` | Accent backgrounds (badges, active nav items) |
| `--destructive` | `oklch(0.65 0.22 25)` | Errors, delete actions, failed states |
| `--success` | `oklch(0.65 0.14 145)` | Success states, positive changes |
| `--warning` | `oklch(0.72 0.16 85)` | Warnings, approaching limits |
| `--info` | `oklch(0.6 0.12 250)` | Informational messages, tips |

### Light Mode
| Token | Value | Usage |
|-------|-------|-------|
| `--background` | `oklch(0.985 0.002 260)` | Page background (warm white with blue tint) |
| `--surface` | `oklch(1 0 0)` | Cards, elevated panels (pure white) |
| `--surface-hover` | `oklch(0.97 0.002 260)` | Hover state |
| `--border` | `oklch(0.91 0.005 260)` | Borders, dividers |
| `--border-subtle` | `oklch(0.95 0.003 260)` | Subtle separators |
| `--text` | `oklch(0.15 0.005 260)` | Primary text |
| `--text-secondary` | `oklch(0.4 0.005 260)` | Secondary text |
| `--text-muted` | `oklch(0.55 0.005 260)` | Muted text |
| `--accent` | `oklch(0.45 0.14 165)` | Accent (darkened for light bg contrast) |
| `--accent-hover` | `oklch(0.4 0.14 165)` | Accent hover |
| `--accent-muted` | `oklch(0.45 0.14 165 / 0.1)` | Accent backgrounds |
| `--destructive` | `oklch(0.5 0.22 25)` | Errors |
| `--success` | `oklch(0.45 0.14 145)` | Success |
| `--warning` | `oklch(0.55 0.16 85)` | Warnings |
| `--info` | `oklch(0.45 0.12 250)` | Info |

### Chart Colors
Use the existing 5-color chart palette from app.css. These are already well-differentiated and accessible.

### Dark Mode Strategy
Dark mode is the hero experience — design dark first, then adapt for light. Reduce accent saturation slightly for light mode to maintain WCAG AA contrast against white backgrounds.

## Spacing
- **Base unit:** 4px
- **Density:** Comfortable — data-dense where needed (tables, dashboards), spacious for marketing and auth flows
- **Scale:** 2xs(4) xs(8) sm(12) md(16) lg(20) xl(24) 2xl(32) 3xl(48) 4xl(64)
- **Section spacing:** 48-64px between major sections
- **Card padding:** 16px (compact), 24px (default), 32px (spacious)

## Layout
- **Approach:** Grid-disciplined — strict columns, predictable alignment
- **Grid:** Sidebar (220px) + main content area. Content uses responsive 12-column grid
- **Max content width:** 1200px for main content, 380px for auth cards
- **Sidebar:** 220px expanded, 64px collapsed
- **Border radius:** Hierarchical scale
  - `sm` (4px): Badges, inline elements
  - `md` (6px): Buttons, inputs, alerts
  - `lg` (8px): Cards, panels, swatches
  - `xl` (10px): Dashboard container, auth cards
  - `full` (9999px): Badges (pill shape), hero badges

## Motion
- **Approach:** Minimal-functional — only transitions that aid comprehension. No bounce, no spring, no decorative animation
- **Easing:** `ease-out` for entrances, `ease-in` for exits, `ease-in-out` for movement
- **Duration:**
  - Micro (100ms): Hover states, focus rings, color changes
  - Short (200ms): Panel transitions, modal entrance, accordion toggle
  - Medium (300ms): Page-level transitions, sidebar collapse
- **Reduced motion:** Respect `prefers-reduced-motion` — disable all non-essential animation (already implemented in app.css)
- **Page transitions:** Fade + translateY(4px), 200ms ease-out (already implemented in app.css)

## Component Principles
- **Buttons:** Subtle, not oversized. Primary uses accent with dark text. Ghost has no border until hover. Destructive is red, used sparingly
- **Cards:** No shadows. Background differentiation only (bg vs surface). 1px border
- **Tables:** Dense but readable. Sticky headers. Monospace for data cells (timestamps, numbers). Hover row highlight
- **Forms:** Compact inputs with clear focus states (border changes to accent). Labels above inputs, 13px IBM Plex Sans
- **Modals/Dialogs:** Centered, backdrop blur, smooth scale-in
- **Navigation:** Sidebar with collapsible sections. Active state uses accent-muted background. Command palette (⌘K)
- **Badges:** Monospace lowercase text. Pill shape. Three variants: default, accent, destructive
- **Alerts:** Colored dot indicator + text. No icons. Subtle colored background + border
- **Section labels:** `//` comment prefix style (e.g., `// typography`), monospace, uppercase, accent color

## Accessibility
- **Standard:** WCAG 2.1 AA
- **Contrast:** ≥ 4.5:1 for body text, ≥ 3:1 for large text and UI components
- **Keyboard:** Full navigation support, visible focus rings, command palette
- **Screen readers:** Semantic HTML, ARIA attributes where needed
- **Reduced motion:** Respect OS preference (already implemented)
- **Color blindness:** Semantic colors are distinguishable by hue angle separation (teal 165°, red 25°, green 145°, amber 85°, blue 250°)

## Decisions Log
| Date | Decision | Rationale |
|------|----------|-----------|
| 2026-03-22 | Initial design system created | Created by /design-consultation based on product context + competitive research (Linear, Raycast, Vercel, Dub.co, Cal.com) |
| 2026-03-22 | JetBrains Mono for display, IBM Plex Sans for body | User chose developer-facing fonts over Geist Sans — monospace headings signal "built by engineers" |
| 2026-03-22 | Muted teal accent oklch(0.65 0.14 165) | User refined from electric teal to muted — sophisticated, present but not screaming. Distinctive vs blue/violet that Linear/Raycast use |
| 2026-03-22 | Cool blue undertone in neutrals | Subtle depth vs pure achromatic. Hue 260 in all neutral values |
| 2026-03-22 | No card shadows — background-level differentiation only | Ultra-flat, print-like quality. More radical than Linear. Creates clean, uniform surfaces |
| 2026-03-22 | Dark-first design | Dark mode is the hero experience. Design dark first, adapt for light |
