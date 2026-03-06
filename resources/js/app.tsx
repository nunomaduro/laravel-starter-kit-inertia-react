import '../css/app.css';
import './echo';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType, ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { Toaster } from 'sonner';
import { CookieConsentBanner } from './components/cookie-consent-banner';
import { FlashListener } from './components/flash-listener';
import { ThemeFromProps } from './components/theme-from-props';
import { KeyboardShortcutDisplay } from './components/ui/keyboard-shortcut-display';
import { initializeTheme } from './hooks/use-appearance';
import { QueryProvider } from './providers/query-provider';

const appName = import.meta.env.VITE_APP_NAME || 'App';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ).then((module) => {
            const Page = (module as { default: ComponentType<Record<string, unknown>> }).default;
            return function PageWithCookieBanner(
                props: Record<string, unknown>,
            ): ReactNode {
                return (
                    <>
                        <ThemeFromProps />
                        <CookieConsentBanner />
                        <FlashListener />
                        <KeyboardShortcutDisplay />
                        <Page {...props} />
                        <Toaster richColors position="top-right" />
                    </>
                );
            };
        }),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <QueryProvider>
                <App {...props} />
            </QueryProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();

// Page transition animation triggers
router.on('start', () =>
    document.documentElement.classList.add('page-transitioning'),
);
router.on('finish', () =>
    document.documentElement.classList.remove('page-transitioning'),
);
