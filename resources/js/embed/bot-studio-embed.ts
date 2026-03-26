/**
 * Bot Studio Embed Widget — Entry Point
 *
 * Usage (widget mode):
 *   <script src="https://app.example.com/js/bot-studio-embed.js" data-token="YOUR_TOKEN"></script>
 *
 * Usage (standalone mode — set by standalone Blade view):
 *   window.__EMBED_CONFIG__ = { token, baseUrl, mode: 'standalone', agent, theme, conversation_starters };
 *   <script src="/js/bot-studio-embed.js"></script>
 */

import { EmbedWidget } from './embed-widget';

interface WindowEmbedConfig {
    token: string;
    baseUrl: string;
    mode: 'standalone' | 'widget';
    agent: {
        name: string;
        slug: string;
        avatar_url: string | null;
        description: string | null;
    };
    theme: {
        primary_color: string;
        position: 'bottom-right' | 'bottom-left';
        greeting: string;
        placeholder: string;
        show_powered_by: boolean;
    };
    conversation_starters: string[];
}

declare global {
    interface Window {
        __EMBED_CONFIG__?: WindowEmbedConfig;
    }
}

(function init(): void {
    // Check for pre-configured standalone mode
    const preConfig = window.__EMBED_CONFIG__;

    if (preConfig) {
        const root = document.getElementById('embed-root') || document.body;
        const host = document.createElement('div');
        host.id = 'bot-studio-embed-host';
        root.appendChild(host);
        new EmbedWidget(preConfig, host);
        return;
    }

    // Widget mode: find the script tag with data-token
    const scripts = document.querySelectorAll<HTMLScriptElement>('script[data-token]');
    const scriptTag = scripts[scripts.length - 1];

    if (!scriptTag) {
        console.error('[Bot Studio Embed] No script tag with data-token found.');
        return;
    }

    const token = scriptTag.getAttribute('data-token');
    if (!token) {
        console.error('[Bot Studio Embed] data-token attribute is empty.');
        return;
    }

    // Determine base URL from the script src
    const src = scriptTag.getAttribute('src') || '';
    let baseUrl = '';

    try {
        const url = new URL(src, window.location.href);
        baseUrl = url.origin;
    } catch {
        baseUrl = window.location.origin;
    }

    // Fetch config from API
    fetch(`${baseUrl}/api/embed/${token}/config`)
        .then((res) => {
            if (!res.ok) {
                throw new Error(`Config request failed: ${res.status}`);
            }
            return res.json();
        })
        .then(
            (data: {
                agent: WindowEmbedConfig['agent'];
                theme: WindowEmbedConfig['theme'];
                conversation_starters: string[];
            }) => {
                const host = document.createElement('div');
                host.id = 'bot-studio-embed-host';
                document.body.appendChild(host);

                new EmbedWidget(
                    {
                        token,
                        baseUrl,
                        mode: 'widget',
                        agent: data.agent,
                        theme: data.theme,
                        conversation_starters: data.conversation_starters,
                    },
                    host,
                );
            },
        )
        .catch((err) => {
            console.error('[Bot Studio Embed] Failed to initialize:', err);
        });
})();
