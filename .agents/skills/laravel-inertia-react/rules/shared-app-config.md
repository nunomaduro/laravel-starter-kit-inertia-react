---
section: shared-data
priority: medium
description: Share app config and feature flags through Inertia middleware
keywords: [config, settings, feature-flags, environment, configuration]
---

# Shared App Configuration

Share application configuration through Inertia's middleware to provide consistent access to app settings, feature flags, and environment-specific values.

## Bad Example

```tsx
// Anti-pattern: Hardcoding configuration values
export default function Footer() {
  return (
    <footer>
      <p>Contact: support@example.com</p>
      <p>Version: 1.0.0</p>
    </footer>
  );
}

// Anti-pattern: Fetching config separately
const [config, setConfig] = useState(null);

useEffect(() => {
  fetch('/api/config').then(res => res.json()).then(setConfig);
}, []);

// Anti-pattern: Using environment variables directly in React
const apiUrl = process.env.REACT_APP_API_URL; // Won't work in Inertia
```

## Good Example

```tsx
// app/Http/Middleware/HandleInertiaRequests.php (Laravel)
/*
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'locale' => app()->getLocale(),
                'timezone' => config('app.timezone'),
                'version' => config('app.version', '1.0.0'),
            ],
            'config' => [
                'contact_email' => config('site.contact_email'),
                'support_phone' => config('site.support_phone'),
                'social' => [
                    'twitter' => config('site.social.twitter'),
                    'facebook' => config('site.social.facebook'),
                    'linkedin' => config('site.social.linkedin'),
                ],
            ],
            'features' => [
                'dark_mode' => config('features.dark_mode', false),
                'notifications' => config('features.notifications', true),
                'two_factor' => config('features.two_factor', false),
                'api_access' => config('features.api_access', false),
            ],
            'settings' => fn () => $request->user()?->settings ?? [],
        ]);
    }
}
*/

// resources/js/types/index.d.ts
export interface AppConfig {
  name: string;
  url: string;
  locale: string;
  timezone: string;
  version: string;
}

export interface SiteConfig {
  contact_email: string;
  support_phone: string;
  social: {
    twitter?: string;
    facebook?: string;
    linkedin?: string;
  };
}

export interface Features {
  dark_mode: boolean;
  notifications: boolean;
  two_factor: boolean;
  api_access: boolean;
}

export interface PageProps {
  auth: { user: User | null };
  flash: FlashMessages;
  app: AppConfig;
  config: SiteConfig;
  features: Features;
  settings: Record<string, unknown>;
}

// resources/js/hooks/useConfig.ts
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export function useApp() {
  const { app } = usePage<PageProps>().props;
  return app;
}

export function useConfig() {
  const { config } = usePage<PageProps>().props;
  return config;
}

export function useFeatures() {
  const { features } = usePage<PageProps>().props;

  return {
    ...features,
    isEnabled: (feature: keyof Features) => features[feature] === true,
  };
}

export function useSettings() {
  const { settings } = usePage<PageProps>().props;
  return settings;
}

// resources/js/Components/FeatureFlag.tsx
import { useFeatures } from '@/hooks/useConfig';
import { Features } from '@/types';
import { ReactNode } from 'react';

interface FeatureFlagProps {
  feature: keyof Features;
  children: ReactNode;
  fallback?: ReactNode;
}

export default function FeatureFlag({ feature, children, fallback = null }: FeatureFlagProps) {
  const { isEnabled } = useFeatures();

  return isEnabled(feature) ? <>{children}</> : <>{fallback}</>;
}

// resources/js/Components/Footer.tsx
import { useApp, useConfig } from '@/hooks/useConfig';
import ExternalLink from '@/Components/ExternalLink';

export default function Footer() {
  const app = useApp();
  const config = useConfig();

  return (
    <footer className="bg-gray-800 py-8 text-gray-300">
      <div className="mx-auto max-w-7xl px-4">
        <div className="grid grid-cols-3 gap-8">
          {/* App info */}
          <div>
            <h3 className="text-lg font-semibold text-white">{app.name}</h3>
            <p className="mt-2 text-sm">Version {app.version}</p>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-lg font-semibold text-white">Contact</h3>
            <p className="mt-2">
              <a href={`mailto:${config.contact_email}`} className="hover:text-white">
                {config.contact_email}
              </a>
            </p>
            <p>
              <a href={`tel:${config.support_phone}`} className="hover:text-white">
                {config.support_phone}
              </a>
            </p>
          </div>

          {/* Social */}
          <div>
            <h3 className="text-lg font-semibold text-white">Follow Us</h3>
            <div className="mt-2 flex gap-4">
              {config.social.twitter && (
                <ExternalLink href={config.social.twitter}>Twitter</ExternalLink>
              )}
              {config.social.facebook && (
                <ExternalLink href={config.social.facebook}>Facebook</ExternalLink>
              )}
              {config.social.linkedin && (
                <ExternalLink href={config.social.linkedin}>LinkedIn</ExternalLink>
              )}
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}

// resources/js/Pages/Settings/Index.tsx
import FeatureFlag from '@/Components/FeatureFlag';
import { useFeatures } from '@/hooks/useConfig';

export default function Settings() {
  const { dark_mode, two_factor, api_access } = useFeatures();

  return (
    <div className="space-y-6">
      <h1>Settings</h1>

      {/* Feature flag component */}
      <FeatureFlag feature="dark_mode">
        <section>
          <h2>Appearance</h2>
          <DarkModeToggle />
        </section>
      </FeatureFlag>

      <FeatureFlag feature="two_factor">
        <section>
          <h2>Two-Factor Authentication</h2>
          <TwoFactorSetup />
        </section>
      </FeatureFlag>

      {/* Conditional rendering with hook */}
      {api_access && (
        <section>
          <h2>API Access</h2>
          <ApiTokenManager />
        </section>
      )}
    </div>
  );
}
```

## Why

Sharing app configuration provides:

1. **Centralized Config**: All configuration flows from Laravel to React
2. **Type Safety**: TypeScript interfaces ensure config structure
3. **Feature Flags**: Easy toggle of features without code changes
4. **Environment Handling**: Correct values for dev/staging/production
5. **No Hardcoding**: Configuration values are maintainable in one place
6. **Lazy Loading**: Use closures for user-specific settings
7. **SSR Compatible**: Works correctly with server-side rendering
