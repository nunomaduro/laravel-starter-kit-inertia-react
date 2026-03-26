---
title: Prevent XSS in React and Inertia.js
impact: HIGH
impactDescription: Prevents script injection that executes in users' browsers
tags: security, xss, react, inertia, dompurify, owasp-a03
---

## Prevent XSS in React and Inertia.js

**Impact: HIGH (Prevents script injection that executes in users' browsers)**

## Why It Matters

- **Risk**: Attackers inject `<script>` tags or event handlers into user-rendered HTML that execute in the browser of every user who views the infected content
- **Impact**: Session hijacking, credential theft, malicious redirects, defacement
- **OWASP**: A03:2021 — applies to React even though React auto-escapes JSX expressions

React auto-escapes `{variable}` expressions. However, `dangerouslySetInnerHTML` bypasses this entirely. Any teacher-entered, admin-entered, or user-generated rich text rendered with `dangerouslySetInnerHTML` without sanitization is a stored XSS vulnerability.

## Incorrect

```tsx
// ❌ dangerouslySetInnerHTML without sanitization
// Teacher can inject: <script>document.cookie</script> in their notes
<div
    className="prose prose-sm"
    dangerouslySetInnerHTML={{ __html: sessionNote.notes }}
/>
```

```tsx
// ❌ Class description rendered without sanitization
// Admin or teacher could inject scripts via the description field
<div dangerouslySetInnerHTML={{ __html: classData.description }} />
```

```tsx
// ❌ href from user input without scheme validation
// javascript:alert(1) is a valid href that executes script on click
<a href={user.website}>Visit Website</a>
```

```tsx
// ❌ eval() or new Function() with user-controlled strings
const fn = new Function(userInput)  // Executes arbitrary user code
eval(userDefinedExpression)
```

**Problems:**
- `dangerouslySetInnerHTML` passes raw HTML directly to the DOM — no React escaping
- A teacher/admin with edit access becomes an attack vector for all users viewing their content
- `javascript:` scheme in `href` executes on click even in React
- `eval()` and `new Function()` with user strings allow arbitrary code execution

## Correct

### Install DOMPurify

```bash
npm install dompurify @types/dompurify
```

### Sanitize All User-Supplied HTML

```tsx
import DOMPurify from 'dompurify';

// ✅ Sanitize before rendering — DOMPurify strips dangerous tags and attributes
<div
    className="prose prose-sm dark:prose-invert max-w-none"
    dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(sessionNote.notes) }}
/>

// ✅ Same for any teacher/admin/user-entered rich text
<div
    className="prose prose-sm dark:prose-invert max-w-none"
    dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(classData.description) }}
/>
```

### Create a Reusable Safe HTML Component

```tsx
import DOMPurify from 'dompurify';

interface SafeHtmlProps {
    html: string;
    className?: string;
}

// ✅ Reusable component — use everywhere instead of raw dangerouslySetInnerHTML
export function SafeHtml({ html, className }: SafeHtmlProps) {
    return (
        <div
            className={className}
            dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(html) }}
        />
    );
}

// Usage
<SafeHtml html={sessionNote.notes} className="prose prose-sm dark:prose-invert" />
<SafeHtml html={classData.description} className="prose prose-sm" />
```

### Validate URL Scheme Before Using in href

```tsx
// ✅ Validate scheme before setting href — blocks javascript: URLs
function getSafeUrl(url: string): string {
    if (url.startsWith('https://') || url.startsWith('http://')) {
        return url;
    }
    return '#';
}

<a href={getSafeUrl(user.website)} target="_blank" rel="noopener noreferrer">
    Visit Website
</a>
```

### Server-Side Sanitization as an Extra Layer

```php
<?php

declare(strict_types=1);

use HTMLPurifier;
use HTMLPurifier_Config;

// ✅ Sanitize rich text on the server before storing (belt-and-suspenders)
class SessionNoteController extends Controller
{
    public function store(StoreSessionNoteRequest $request, ClassSession $session): RedirectResponse
    {
        $config   = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        SessionNote::create([
            'session_id' => $session->id,
            'notes'      => $purifier->purify($request->validated('notes')),
            'homework'   => $purifier->purify($request->validated('homework', '')),
        ]);

        return redirect()->back()->with('success', 'Session notes saved.');
    }
}
```

## What DOMPurify Allows vs. Strips

| Allowed (safe) | Stripped (dangerous) |
|----------------|---------------------|
| `<p>`, `<b>`, `<i>`, `<ul>`, `<li>` | `<script>`, `<iframe>`, `<object>` |
| `<a href="https://...">` | `<a href="javascript:...">` |
| `<img src="https://...">` | `onclick`, `onerror`, `onload` attributes |
| `<h1>`–`<h6>`, `<blockquote>` | `<style>` with expression() |

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `DOMPurify.sanitize(html)` | All `dangerouslySetInnerHTML` usage |
| `<SafeHtml html={...} />` | Reusable sanitized renderer |
| `getSafeUrl(url)` | User-supplied `href` or `src` attributes |
| Server-side HTMLPurifier | Belt-and-suspenders for stored content |
| `{{ }}` in Blade | User content in Blade templates (auto-escaped) |

Reference: [DOMPurify](https://github.com/cure53/DOMPurify) | [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
