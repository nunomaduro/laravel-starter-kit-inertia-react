import js from '@eslint/js';
import eslintReact from '@eslint-react/eslint-plugin';
import prettier from 'eslint-config-prettier/flat';
import globals from 'globals';
import typescript from 'typescript-eslint';

/** @type {import('eslint').Linter.Config[]} */
export default [
    js.configs.recommended,
    ...typescript.configs.recommended,
    {
        ...eslintReact.configs['recommended-typescript'],
        languageOptions: {
            globals: {
                ...globals.browser,
            },
        },
        rules: {
            // Intentional setState-in-useEffect (sync from props, mount, subscriptions) — re-enable and fix per-file if desired
            '@eslint-react/hooks-extra/no-direct-set-state-in-use-effect': 'off',
            // Stable keys not always available (e.g. static lists, chart cells) — prefer id/name when present
            '@eslint-react/no-array-index-key': 'off',
            // Third-party / layout patterns
            '@eslint-react/dom/no-dangerously-set-innerhtml': 'off',
            '@eslint-react/no-children-to-array': 'off',
            '@eslint-react/no-children-map': 'off',
            '@eslint-react/web-api/no-leaked-timeout': 'off',
            '@eslint-react/web-api/no-leaked-event-listener': 'off',
        },
    },
    {
        ignores: [
            'resources/js/actions/**',
            'vendor',
            'node_modules',
            'node_modules/**',
            'public',
            'public/**',
            'bootstrap/ssr',
            'tailwind.config.js',
            'storybook-static',
            'storybook-static/**',
        ],
    },
    prettier, // Turn off all rules that might conflict with Prettier
];
