<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $definition->name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: {{ $theme['primary_color'] }};
            --bg: #0a0a0a;
            --surface: #141414;
            --border: #262626;
            --text: #fafafa;
            --text-muted: #a1a1aa;
            --font-sans: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-mono: 'JetBrains Mono', 'SF Mono', 'Fira Code', monospace;
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg);
            color: var(--text);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }

        .header-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .header-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .header-avatar svg {
            width: 20px;
            height: 20px;
            color: var(--text-muted);
        }

        .header-name {
            font-family: var(--font-mono);
            font-weight: 700;
            font-size: 14px;
            letter-spacing: -0.02em;
        }

        .chat-container {
            flex: 1;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-avatar">
            @if($definition->avatar_path)
                <img src="{{ $definition->avatar_path }}" alt="{{ $definition->name }}">
            @else
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/>
                </svg>
            @endif
        </div>
        <span class="header-name">{{ $definition->name }}</span>
    </div>
    <div class="chat-container" id="embed-root"></div>

    <script>
        window.__EMBED_CONFIG__ = {
            token: @json($token),
            baseUrl: @json(url('/')),
            mode: 'standalone',
            agent: {
                name: @json($definition->name),
                slug: @json($definition->slug),
                avatar_url: @json($definition->avatar_path),
                description: @json($definition->description),
            },
            theme: @json($theme),
            conversation_starters: @json($definition->conversation_starters ?? []),
        };
    </script>
    <script src="{{ asset('build/js/bot-studio-embed.js') }}"></script>
</body>
</html>
