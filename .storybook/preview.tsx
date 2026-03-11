import type { Preview } from '@storybook/react';
import React from 'react';

import '../resources/css/app.css';

// ---------------------------------------------------------------------------
// Inertia mock stubs
// These prevent "Cannot read properties of undefined" errors in components
// that call usePage(), router.*, Link, or useForm at import / render time.
// ---------------------------------------------------------------------------
const mockPage = {
    component: '',
    props: {
        auth: { user: null },
        flash: {},
        errors: {},
        theme: {
            preset: 'default',
            radius: 'default',
            font: 'inter',
            baseColor: 'slate',
            dark: 'navy',
            primary: 'indigo',
            light: 'slate',
            skin: 'shadow',
            canCustomize: false,
            userMode: 'system',
        },
        ziggy: { url: 'http://localhost', port: null, defaults: {}, routes: {} },
    },
    url: '/',
    version: null,
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
(globalThis as any).__inertia_page = mockPage;

// Mock @inertiajs/react so components that import from it work in Storybook
// without a real Inertia / Laravel backend.
// eslint-disable-next-line @typescript-eslint/no-require-imports
const inertiaModule = require('@inertiajs/react');

if (!inertiaModule.__storybookMocked) {
    // usePage – return a stable mock page object (mock; name must match Inertia API)
     
    inertiaModule.usePage = () => mockPage;

    // router – stub all common methods
    inertiaModule.router = {
        get: () => {},
        post: () => {},
        put: () => {},
        patch: () => {},
        delete: () => {},
        visit: () => {},
        reload: () => {},
        on: () => () => {},
        off: () => {},
    };

    // useForm – return a minimal form helper (mock; name must match Inertia API)
     
    inertiaModule.useForm = (initialData?: Record<string, unknown>) => ({
        data: initialData ?? {},
        setData: () => {},
        post: () => {},
        put: () => {},
        patch: () => {},
        delete: () => {},
        submit: () => {},
        reset: () => {},
        clearErrors: () => {},
        errors: {},
        processing: false,
        recentlySuccessful: false,
        isDirty: false,
        wasSuccessful: false,
        transform: () => {},
        defaults: () => {},
        cancel: () => {},
    });

    // Link – render a plain <a> tag
    inertiaModule.Link = (
            { ref, href, children, ...props },
        ) => (
            <a href={href} ref={ref} {...(props as React.AnchorHTMLAttributes<HTMLAnchorElement>)}>
                {children}
            </a>
        );
    inertiaModule.Link.displayName = 'Link';

    inertiaModule.__storybookMocked = true;
}

// ---------------------------------------------------------------------------
// Helper: apply Tailux data-* attributes to <html> from toolbar globals
// ---------------------------------------------------------------------------
function applyThemeAttributes(globals: Record<string, string>) {
    const root = document.documentElement;

    // Dark theme
    if (globals['darkTheme']) {
        root.setAttribute('data-theme-dark', globals['darkTheme']);
    }

    // Primary color
    if (globals['primaryColor']) {
        root.setAttribute('data-theme-primary', globals['primaryColor']);
    }

    // Light scheme
    if (globals['lightTheme']) {
        root.setAttribute('data-theme-light', globals['lightTheme']);
    }

    // Card skin
    if (globals['cardSkin']) {
        root.setAttribute('data-card-skin', globals['cardSkin']);
    }

    // Border radius
    if (globals['radius']) {
        root.setAttribute('data-radius', globals['radius']);
    }

    // Dark / light / system mode
    const mode = globals['darkMode'];
    if (mode === 'dark') {
        root.classList.add('dark');
        root.style.colorScheme = 'dark';
    } else if (mode === 'light') {
        root.classList.remove('dark');
        root.style.colorScheme = 'light';
    } else {
        // system
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', prefersDark);
        root.style.colorScheme = prefersDark ? 'dark' : 'light';
    }
}

// ---------------------------------------------------------------------------
// Preview configuration
// ---------------------------------------------------------------------------
const preview: Preview = {
    globalTypes: {
        darkMode: {
            description: 'Color scheme mode',
            toolbar: {
                title: 'Mode',
                icon: 'circlehollow',
                items: [
                    { value: 'system', title: 'System', icon: 'browser' },
                    { value: 'light', title: 'Light', icon: 'sun' },
                    { value: 'dark', title: 'Dark', icon: 'moon' },
                ],
                dynamicTitle: true,
            },
        },
        darkTheme: {
            description: 'Tailux dark theme',
            toolbar: {
                title: 'Dark Theme',
                icon: 'paintbrush',
                items: [
                    { value: 'navy', title: 'Navy' },
                    { value: 'mirage', title: 'Mirage' },
                    { value: 'mint', title: 'Mint' },
                    { value: 'black', title: 'Black' },
                    { value: 'cinder', title: 'Cinder' },
                ],
                dynamicTitle: true,
            },
        },
        primaryColor: {
            description: 'Tailux primary color',
            toolbar: {
                title: 'Primary',
                icon: 'circle',
                items: [
                    { value: 'indigo', title: 'Indigo' },
                    { value: 'blue', title: 'Blue' },
                    { value: 'green', title: 'Green' },
                    { value: 'amber', title: 'Amber' },
                    { value: 'purple', title: 'Purple' },
                    { value: 'rose', title: 'Rose' },
                ],
                dynamicTitle: true,
            },
        },
        lightTheme: {
            description: 'Tailux light scheme',
            toolbar: {
                title: 'Light Scheme',
                icon: 'grid',
                items: [
                    { value: 'slate', title: 'Slate' },
                    { value: 'gray', title: 'Gray' },
                    { value: 'neutral', title: 'Neutral' },
                ],
                dynamicTitle: true,
            },
        },
        cardSkin: {
            description: 'Card skin',
            toolbar: {
                title: 'Card Skin',
                icon: 'box',
                items: [
                    { value: 'shadow', title: 'Shadow' },
                    { value: 'bordered', title: 'Bordered' },
                    { value: 'flat', title: 'Flat' },
                    { value: 'elevated', title: 'Elevated' },
                ],
                dynamicTitle: true,
            },
        },
        radius: {
            description: 'Border radius',
            toolbar: {
                title: 'Radius',
                icon: 'photo',
                items: [
                    { value: 'none', title: 'None' },
                    { value: 'sm', title: 'SM' },
                    { value: 'default', title: 'Default' },
                    { value: 'md', title: 'MD' },
                    { value: 'lg', title: 'LG' },
                    { value: 'full', title: 'Full' },
                ],
                dynamicTitle: true,
            },
        },
    },
    initialGlobals: {
        darkMode: 'system',
        darkTheme: 'navy',
        primaryColor: 'indigo',
        lightTheme: 'slate',
        cardSkin: 'shadow',
        radius: 'default',
    },
    decorators: [
        (Story, context) => {
            applyThemeAttributes(context.globals as Record<string, string>);
            return <Story />;
        },
    ],
    parameters: {
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/i,
            },
        },
    },
};

export default preview;
