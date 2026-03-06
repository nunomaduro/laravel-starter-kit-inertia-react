/**
 * Laravel Echo bootstrap for Reverb (Pusher protocol).
 * Import this in app.tsx when using real-time broadcasting, e.g.:
 *
 *   import './echo';
 *
 * Then use window.Echo in components to listen to private/user channels.
 * Requires BROADCAST_CONNECTION=reverb and VITE_REVERB_* env vars.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        Echo?: Echo<any>;
        Pusher?: typeof Pusher;
    }
}

const key = import.meta.env.VITE_REVERB_APP_KEY;
const host = import.meta.env.VITE_REVERB_HOST ?? 'localhost';
const port = import.meta.env.VITE_REVERB_PORT ?? '8080';
const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

if (key) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
