---
section: navigation
priority: high
description: Use standard anchor tags for external links and file downloads
keywords: [external, links, download, anchor, security, noopener]
---

# External Links Handling

Use standard anchor tags for external links and file downloads, reserving Inertia's Link component for internal navigation only.

## Bad Example

```tsx
// Anti-pattern: Using Inertia Link for external URLs
import { Link } from '@inertiajs/react';

export default function Footer() {
  return (
    <footer>
      {/* These will cause Inertia to try to handle them as internal routes */}
      <Link href="https://twitter.com/mycompany">Twitter</Link>
      <Link href="https://github.com/mycompany">GitHub</Link>
      <Link href="/files/document.pdf">Download PDF</Link>
    </footer>
  );
}

// Anti-pattern: Using router.visit for external URLs
const openExternal = () => {
  router.visit('https://example.com'); // Will fail or cause unexpected behavior
};
```

## Good Example

```tsx
// resources/js/Components/ExternalLink.tsx
import { AnchorHTMLAttributes, ReactNode } from 'react';

interface ExternalLinkProps extends AnchorHTMLAttributes<HTMLAnchorElement> {
  href: string;
  children: ReactNode;
  showIcon?: boolean;
}

export default function ExternalLink({
  href,
  children,
  showIcon = true,
  className = '',
  ...props
}: ExternalLinkProps) {
  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      className={`inline-flex items-center gap-1 ${className}`}
      {...props}
    >
      {children}
      {showIcon && (
        <svg
          className="h-4 w-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
          />
        </svg>
      )}
    </a>
  );
}

// resources/js/Components/DownloadLink.tsx
interface DownloadLinkProps {
  href: string;
  filename?: string;
  children: ReactNode;
  className?: string;
}

export default function DownloadLink({
  href,
  filename,
  children,
  className = '',
}: DownloadLinkProps) {
  return (
    <a
      href={href}
      download={filename}
      className={`inline-flex items-center gap-1 ${className}`}
    >
      <svg
        className="h-4 w-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={2}
          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
        />
      </svg>
      {children}
    </a>
  );
}

// resources/js/Pages/Resources.tsx
import { Link } from '@inertiajs/react';
import ExternalLink from '@/Components/ExternalLink';
import DownloadLink from '@/Components/DownloadLink';

interface ResourcesProps {
  documents: Document[];
  socialLinks: SocialLink[];
}

export default function Resources({ documents, socialLinks }: ResourcesProps) {
  return (
    <div className="space-y-8">
      {/* Internal navigation uses Inertia Link */}
      <nav className="flex gap-4">
        <Link href={route('resources.guides')}>Guides</Link>
        <Link href={route('resources.tutorials')}>Tutorials</Link>
        <Link href={route('resources.faq')}>FAQ</Link>
      </nav>

      {/* External links use regular anchors */}
      <section>
        <h2>Follow Us</h2>
        <div className="flex gap-4">
          {socialLinks.map((link) => (
            <ExternalLink
              key={link.id}
              href={link.url}
              className="text-blue-600 hover:text-blue-800"
            >
              {link.platform}
            </ExternalLink>
          ))}
        </div>
      </section>

      {/* File downloads use regular anchors with download attribute */}
      <section>
        <h2>Documents</h2>
        <ul className="space-y-2">
          {documents.map((doc) => (
            <li key={doc.id}>
              <DownloadLink
                href={doc.url}
                filename={doc.filename}
                className="text-blue-600 hover:text-blue-800"
              >
                {doc.name} ({doc.size})
              </DownloadLink>
            </li>
          ))}
        </ul>
      </section>

      {/* Mixed content example */}
      <section>
        <h2>Getting Started</h2>
        <p>
          Read our{' '}
          <Link href={route('docs.getting-started')} className="text-blue-600">
            getting started guide
          </Link>{' '}
          or check out the{' '}
          <ExternalLink
            href="https://github.com/company/repo"
            className="text-blue-600"
          >
            GitHub repository
          </ExternalLink>
          .
        </p>
      </section>
    </div>
  );
}

// Utility function to determine link type
function isExternalUrl(url: string): boolean {
  try {
    const urlObj = new URL(url, window.location.origin);
    return urlObj.origin !== window.location.origin;
  } catch {
    return false;
  }
}

// Smart Link component that auto-detects external URLs
import { Link as InertiaLink } from '@inertiajs/react';

interface SmartLinkProps {
  href: string;
  children: ReactNode;
  className?: string;
}

export function SmartLink({ href, children, className }: SmartLinkProps) {
  if (isExternalUrl(href)) {
    return (
      <a
        href={href}
        target="_blank"
        rel="noopener noreferrer"
        className={className}
      >
        {children}
      </a>
    );
  }

  return (
    <InertiaLink href={href} className={className}>
      {children}
    </InertiaLink>
  );
}
```

## Why

Proper handling of external links and downloads is important because:

1. **Correct Behavior**: Inertia's Link is designed for internal SPA navigation only
2. **Security**: External links should use `rel="noopener noreferrer"` to prevent tabnabbing
3. **User Expectations**: External links should open in new tabs with visual indicators
4. **Downloads**: File downloads need regular anchors with download attribute
5. **SEO**: Search engines correctly follow standard anchor tags
6. **Accessibility**: Screen readers announce external links appropriately
7. **Error Prevention**: Using Inertia Link for external URLs causes errors or unexpected behavior
