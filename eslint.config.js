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
