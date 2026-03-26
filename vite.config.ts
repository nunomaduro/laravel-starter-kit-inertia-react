import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        wayfinder({
            formVariants: true,
            command: 'php -d memory_limit=512M artisan wayfinder:generate',
        }),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    build: {
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'resources/js/app.tsx'),
                'bot-studio-embed': resolve(
                    __dirname,
                    'resources/js/embed/bot-studio-embed.ts',
                ),
            },
            output: {
                entryFileNames: (chunkInfo) => {
                    if (chunkInfo.name === 'bot-studio-embed') {
                        return 'js/bot-studio-embed.js';
                    }
                    return 'assets/[name]-[hash].js';
                },
            },
        },
    },
});
