{{--
Blade theme injection contract (Phase 0):
- Initial paint is driven by an effective appearance: theme.default_appearance from config (after
  SettingsOverlayServiceProvider) or, when org/user overrides apply, the resolved value.
- Keys used for first paint: theme.preset (via data-theme / CSS vars in themes.css), and
  theme.default_appearance for html background and optional html.dark.
- Theme presets override CSS custom properties only; @theme in app.css stays as-is.
--}}
@php
    $theme = $theme ?? ['default_appearance' => 'system', 'font' => 'instrument-sans', 'preset' => 'default', 'radius' => 'default', 'base_color' => 'neutral'];
    $effectiveAppearance = $appearance ?? ($theme['default_appearance'] ?? 'system');
    $themePreset = $theme['preset'] ?? 'default';
    $themeRadius = $theme['radius'] ?? 'default';
    $themeFont = $theme['font'] ?? 'instrument-sans';
    $themeBaseColor = $theme['base_color'] ?? 'neutral';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ $themePreset }}"
      data-radius="{{ $themeRadius }}"
      data-font="{{ $themeFont }}"
      data-base-color="{{ $themeBaseColor }}"
      @class(['dark' => $effectiveAppearance === 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @isset($seo)
        @if(!empty($seo['meta_description']))
        <meta name="description" content="{{ $seo['meta_description'] }}">
        @endif
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $seo['meta_title'] ?? config('app.name') }}">
        @if(!empty($seo['meta_description']))
        <meta property="og:description" content="{{ $seo['meta_description'] }}">
        @endif
        <meta property="og:url" content="{{ $seo['current_url'] ?? $seo['app_url'] ?? url()->current() }}">
        @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seo['meta_title'] ?? config('app.name') }}">
        @if(!empty($seo['meta_description']))
        <meta name="twitter:description" content="{{ $seo['meta_description'] }}">
        @endif
        @endisset

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $effectiveAppearance }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style: first-paint background to match theme (no flash before hydration) --}}
        <style>
            html { background-color: oklch(1 0 0); }
            html.dark { background-color: oklch(0.145 0 0); }
        </style>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        @if(($theme['font'] ?? 'instrument-sans') === 'geist-sans')
        <link href="https://fonts.bunny.net/css?family=geist:400,500,600" rel="stylesheet" />
        @else
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @endif

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <x-impersonate::banner />
    </body>
</html>
