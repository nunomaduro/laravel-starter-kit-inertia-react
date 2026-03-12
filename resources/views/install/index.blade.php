<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f0f0f;
            color: #e5e5e5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            width: 100%;
            max-width: 540px;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 2.5rem;
        }

        /* ── Logo ─────────────────────────────────────── */
        .logo { display: flex; align-items: center; gap: 0.625rem; margin-bottom: 1.75rem; }
        .logo-icon { width: 32px; height: 32px; background: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .logo-icon svg { width: 18px; height: 18px; }
        .logo-name { font-size: 1rem; font-weight: 600; color: #fff; }

        /* ── Progress bar (installer steps — use bar instead of dots) ── */
        .progress-wrap { margin-bottom: 1.75rem; }
        .progress-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem; }
        .progress-step-name { font-size: 0.75rem; font-weight: 600; color: #a3a3a3; text-transform: uppercase; letter-spacing: 0.06em; }
        .progress-count { font-size: 0.75rem; color: #737373; }
        .progress-bar-track { height: 3px; background: #2a2a2a; border-radius: 99px; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: #fff; border-radius: 99px; transition: width 0.2s ease; }

        /* ── Typography ─────────────────────────────────── */
        h1 { font-size: 1.25rem; font-weight: 600; color: #fff; margin-bottom: 0.375rem; }
        .subtitle { font-size: 0.8125rem; color: #737373; line-height: 1.6; margin-bottom: 1.5rem; }
        .badge-opt { font-size: 0.6875rem; font-weight: 400; color: #525252; vertical-align: middle; margin-left: 0.375rem; }

        /* ── Form fields ─────────────────────────────────── */
        .field { margin-bottom: 1rem; }
        .field label { display: block; font-size: 0.8125rem; color: #a3a3a3; margin-bottom: 0.375rem; }
        .field input, .field select, .field textarea {
            width: 100%;
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            color: #e5e5e5;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }
        .field input:focus-visible, .field select:focus-visible, .field textarea:focus-visible {
            border-color: #737373;
            box-shadow: 0 0 0 2px #0f0f0f, 0 0 0 4px #525252;
        }
        .field select { cursor: pointer; }
        .hint { font-size: 0.75rem; color: #525252; margin-top: 0.3rem; }

        /* ── Radio / toggle groups ─────────────────────── */
        .radio-group { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
        .radio-option { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border: 1px solid #2a2a2a; border-radius: 8px; cursor: pointer; transition: border-color 0.2s; }
        .radio-option:focus-within { border-color: #737373; box-shadow: 0 0 0 2px #0f0f0f, 0 0 0 4px #525252; outline: none; }
        .radio-option:has(input:checked) { border-color: #525252; }
        .radio-option input { margin-top: 0.2rem; flex-shrink: 0; }
        .radio-label { font-size: 0.875rem; font-weight: 500; color: #e5e5e5; }
        .radio-desc { font-size: 0.75rem; color: #737373; margin-top: 0.125rem; }

        /* ── Toggle ──────────────────────────────────────── */
        .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0; border-bottom: 1px solid #1f1f1f; }
        .toggle-row:last-child { border-bottom: none; }
        .toggle-label { font-size: 0.875rem; color: #e5e5e5; }
        .toggle-desc { font-size: 0.75rem; color: #525252; margin-top: 0.125rem; }
        .toggle-wrap { position: relative; }
        .toggle-wrap input[type=checkbox] { appearance: none; width: 36px; height: 20px; min-height: 20px; background: #2a2a2a; border-radius: 99px; cursor: pointer; transition: background 0.2s; }
        .toggle-wrap input[type=checkbox]:checked { background: #fff; }
        .toggle-wrap input[type=checkbox]:focus-visible { outline: none; box-shadow: 0 0 0 2px #0f0f0f, 0 0 0 4px #525252; }
        .toggle-wrap input[type=checkbox]::after { content: ''; position: absolute; top: 3px; left: 3px; width: 14px; height: 14px; background: #737373; border-radius: 50%; transition: left 0.2s, background 0.2s; }
        .toggle-wrap input[type=checkbox]:checked::after { left: 19px; background: #0f0f0f; }

        /* ── Extra fields ───────────────────────────────── */
        .extra-fields { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #2a2a2a; }

        /* ── Two-column (stack on narrow) ────────────────── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        @@media (max-width: 480px) { .two-col { grid-template-columns: 1fr; } }

        /* ── Section divider ─────────────────────────────── */
        .section-label { font-size: 0.6875rem; font-weight: 600; color: #525252; text-transform: uppercase; letter-spacing: 0.08em; margin: 1.25rem 0 0.75rem; border-bottom: 1px solid #2a2a2a; padding-bottom: 0.375rem; }

        /* ── Buttons (min 44px touch target, focus ring) ──── */
        .btn { width: 100%; min-height: 44px; padding: 0.6875rem 1rem; border-radius: 8px; border: none; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: opacity 0.2s; margin-top: 1.25rem; }
        .btn:hover { opacity: 0.88; }
        .btn:focus-visible { outline: none; box-shadow: 0 0 0 2px #0f0f0f, 0 0 0 4px #525252; }
        .btn-primary { background: #fff; color: #0f0f0f; }
        .btn-express { background: transparent; border: 1px solid #525252; color: #a3a3a3; margin-top: 0; display: flex; flex-direction: column; gap: 0.25rem; align-items: center; padding: 0.875rem 1rem; min-height: 44px; justify-content: center; }
        .btn-express .btn-express-title { font-size: 0.875rem; font-weight: 500; }
        .btn-express .btn-express-desc { font-size: 0.75rem; color: #525252; font-weight: 400; }
        .btn-secondary { background: transparent; border: 1px solid #525252; color: #a3a3a3; margin-top: 0.5rem; }
        .btn-skip { background: transparent; border: 1px solid #2a2a2a; color: #525252; }
        .btn-link { background: none; border: none; padding: 0; font-size: 0.8125rem; color: #737373; cursor: pointer; }
        .btn-link:hover { color: #a3a3a3; }
        .btn-link:focus-visible { outline: none; border-radius: 4px; box-shadow: 0 0 0 2px #525252; }
        .back-row { margin-bottom: 1rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.375rem; min-height: 44px; padding: 0.25rem 0; font-size: 0.8125rem; color: #737373; text-decoration: none; cursor: pointer; background: none; border: none; font-family: inherit; }
        .back-link:hover { color: #a3a3a3; }
        .back-link:focus-visible { outline: none; border-radius: 6px; box-shadow: 0 0 0 2px #525252; }

        /* ── Alerts ─────────────────────────────────────── */
        .alert { background: #1c0a0a; border: 1px solid #7f1d1d; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8125rem; color: #fca5a5; margin-bottom: 1.25rem; line-height: 1.5; }
        .info-box { background: #0a0f1c; border: 1px solid #1e3a5f; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8125rem; color: #93c5fd; margin-bottom: 1rem; line-height: 1.6; }
        .test-result { border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8125rem; margin-top: 0.75rem; display: none; }
        .test-result.visible { display: block; }
        .test-result.success { background: #052e16; border: 1px solid #166534; color: #86efac; }
        .test-result.error { background: #1c0a0a; border: 1px solid #7f1d1d; color: #fca5a5; }

        /* ── Modules grid ───────────────────────────────── */
        .select-all-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
        .modules-grid { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 0.25rem; }
        .module-option { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border: 1px solid #2a2a2a; border-radius: 8px; cursor: pointer; }
        .module-option:has(input:checked) { border-color: #525252; }
        .module-option input { margin-top: 0.2rem; flex-shrink: 0; }
        .module-option { cursor: pointer; }
        .module-option:focus-within { box-shadow: 0 0 0 2px #0f0f0f, 0 0 0 4px #525252; outline: none; border-radius: 8px; }
        .module-label { font-size: 0.875rem; font-weight: 500; color: #e5e5e5; }
        .module-desc { font-size: 0.75rem; color: #737373; margin-top: 0.125rem; }
        .module-badge { font-size: 0.6875rem; background: #1f2937; color: #93c5fd; border-radius: 4px; padding: 1px 6px; margin-left: 0.375rem; }

        /* ── AI model combobox ───────────────────────────── */
        .ai-model-combobox { position: relative; }
        .ai-model-combobox .ai-model-list { position: absolute; left: 0; right: 0; top: 100%; margin-top: 2px; max-height: 280px; overflow-y: auto; background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; z-index: 50; display: none; box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
        .ai-model-combobox .ai-model-list.visible { display: block; }
        .ai-model-combobox .ai-model-option { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.8125rem; color: #e5e5e5; cursor: pointer; border: none; background: none; width: 100%; text-align: left; font-family: inherit; }
        .ai-model-combobox .ai-model-option:hover { background: #262626; }
        .ai-model-combobox .ai-model-option[aria-selected="true"] { background: #262626; }
        .ai-model-combobox .ai-model-option .ai-model-name { flex: 1; min-width: 0; }
        .ai-model-combobox .ai-model-option .ai-model-pricing { flex-shrink: 0; font-size: 0.75rem; color: #737373; }
        .ai-model-combobox .ai-model-option .ai-model-free { font-size: 0.6875rem; font-weight: 600; color: #22c55e; text-transform: uppercase; }
        .ai-model-combobox .ai-model-list-loading { padding: 0.75rem 1rem; font-size: 0.8125rem; color: #737373; }
    </style>
</head>
<body>
<div class="card">

    {{-- Logo --}}
    <div class="logo">
        <div class="logo-icon">
            <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 2L2 7l8 5 8-5-8-5z" fill="#0f0f0f"/>
                <path d="M2 13l8 5 8-5" stroke="#0f0f0f" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <span class="logo-name">App Installer</span>
    </div>

    {{-- Progress bar --}}
    @php
        $allSteps = ['database', 'migrate', 'admin', 'app', 'tenancy', 'infrastructure', 'mail', 'search', 'ai', 'social', 'storage', 'broadcasting', 'seo', 'monitoring', 'billing', 'integrations', 'theme', 'memory', 'backup', 'features', 'demo'];
        $stepLabels = [
            'database' => 'Database', 'migrate' => 'Tables', 'admin' => 'Admin', 'app' => 'App',
            'tenancy' => 'Tenancy', 'infrastructure' => 'Infrastructure', 'mail' => 'Mail',
            'search' => 'Search', 'ai' => 'AI', 'social' => 'Social Auth',
            'storage' => 'Storage', 'broadcasting' => 'Broadcasting', 'seo' => 'SEO',
            'monitoring' => 'Monitoring', 'billing' => 'Billing', 'integrations' => 'Integrations',
            'theme' => 'Theme', 'memory' => 'AI Memory', 'backup' => 'Backups',
            'features' => 'Feature flags', 'demo' => 'Demo Data',
        ];
        $currentIdx = array_search($step, $allSteps);
        $total = count($allSteps);
        $pct = round(($currentIdx / ($total - 1)) * 100);
    @endphp

    <div class="progress-wrap" role="region" aria-label="Setup progress">
        <div class="progress-header">
            <span class="progress-step-name">{{ $stepLabels[$step] ?? $step }}</span>
            <span class="progress-count" aria-hidden="true">{{ $currentIdx + 1 }} / {{ $total }}</span>
        </div>
        <div class="progress-bar-track" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Step {{ $currentIdx + 1 }} of {{ $total }}">
            <div class="progress-bar-fill" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="alert" role="alert">
            @foreach ($errors->all() as $err){{ $err }}<br>@endforeach
        </div>
    @endif

    @if ($currentIdx > 0)
        <div class="back-row">
            @php
                $optionalAndDemo = ['tenancy', 'infrastructure', 'mail', 'search', 'ai', 'social', 'storage', 'broadcasting', 'seo', 'monitoring', 'billing', 'integrations', 'theme', 'memory', 'backup', 'features', 'demo'];
                $backUrl = in_array($step, $optionalAndDemo) ? route('install', ['back' => 1]) : route('install', ['step' => $allSteps[$currentIdx - 1]]);
            @endphp
            <a href="{{ $backUrl }}" class="back-link" aria-label="Go back to previous step">← Back</a>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 1: Database --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @if ($step === 'database')

        <h1>Database</h1>
        <p class="subtitle">Choose where your application stores data. SQLite requires no server and is perfect for getting started.</p>

        <button type="button" class="btn btn-express" id="express-btn" onclick="startExpressInstall({})">
            <span class="btn-express-title">Quick install — SQLite + defaults, no demo data →</span>
            <span class="btn-express-desc">Uses SQLite, creates admin (superadmin@example.com / password), app name "My App", skips optional steps. Change password after first login.</span>
        </button>
        <div class="field" style="margin-top:1rem">
            <span class="section-label" style="margin-top:0;border:0;padding:0">Quick install with custom settings</span>
            <p class="hint" style="margin-bottom:0.75rem">Completes setup and skips the remaining steps.</p>
            <div class="two-col" style="align-items:end;gap:0.75rem;flex-wrap:wrap">
                <div class="field" style="margin-bottom:0">
                    <label for="express-preset" style="font-size:0.75rem;color:#737373">Preset</label>
                    <select id="express-preset" onchange="expressPresetChange()">
                        <option value="">None — set below</option>
                        <option value="saas">SaaS</option>
                        <option value="internal">Internal tool</option>
                        <option value="ai_first">AI-first</option>
                    </select>
                </div>
                <div class="field" style="margin-bottom:0">
                    <label for="express-tenancy" style="font-size:0.75rem;color:#737373">Tenancy</label>
                    <select id="express-tenancy" onchange="toggleExpressSingleOrg()">
                        <option value="multi">Multi-organization</option>
                        <option value="single">Single-organization</option>
                    </select>
                </div>
                <div class="field" style="margin-bottom:0;display:none" id="express-single-org-wrap">
                    <label for="express-single-org-name" style="font-size:0.75rem;color:#737373">Organization name</label>
                    <input type="text" id="express-single-org-name" placeholder="My Organization">
                </div>
                <div class="field" style="margin-bottom:0">
                    <label for="express-demo" style="font-size:0.75rem;color:#737373">Demo data</label>
                    <select id="express-demo">
                        <option value="none">None</option>
                        <option value="minimal">Minimal (users, orgs, content)</option>
                        <option value="full">Full (all modules)</option>
                    </select>
                </div>
                <div class="field" style="margin-bottom:0">
                    <label for="express-site-name" style="font-size:0.75rem;color:#737373">Site name (optional)</label>
                    <input type="text" id="express-site-name" placeholder="My App">
                </div>
                <div class="field" style="margin-bottom:0">
                    <label for="express-locale" style="font-size:0.75rem;color:#737373">Locale (optional)</label>
                    <select id="express-locale">
                        <option value="">Default (en)</option>
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="pt">Portuguese</option>
                        <option value="it">Italian</option>
                        <option value="nl">Dutch</option>
                        <option value="ja">Japanese</option>
                        <option value="ko">Korean</option>
                        <option value="zh">Chinese</option>
                        <option value="ar">Arabic</option>
                    </select>
                </div>
                <div class="field" style="margin-bottom:0">
                    <label for="express-fallback-locale" style="font-size:0.75rem;color:#737373">Fallback locale (optional)</label>
                    <select id="express-fallback-locale">
                        <option value="">Default (en)</option>
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="pt">Portuguese</option>
                    </select>
                </div>
                <div class="field" style="margin-bottom:0">
                    <label style="font-size:0.75rem;color:#737373">AI provider (optional)</label>
                    <select id="express-ai-provider">
                        <option value="">None — configure later</option>
                        <option value="openrouter">OpenRouter</option>
                        <option value="openai">OpenAI</option>
                        <option value="anthropic">Anthropic</option>
                        <option value="groq">Groq</option>
                        <option value="gemini">Gemini</option>
                        <option value="xai">xAI</option>
                        <option value="deepseek">DeepSeek</option>
                        <option value="mistral">Mistral</option>
                        <option value="ollama">Ollama (local)</option>
                    </select>
                </div>
                <div class="field" style="margin-bottom:0;display:none" id="express-ai-key-wrap">
                    <label style="font-size:0.75rem;color:#737373">API key</label>
                    <input type="password" id="express-ai-api-key" placeholder="sk-…" autocomplete="off">
                </div>
                <div class="field" style="margin-bottom:0;display:none" id="express-ai-model-wrap">
                    <label style="font-size:0.75rem;color:#737373">Default model (optional)</label>
                    <input type="text" id="express-ai-model" placeholder="e.g. openai/gpt-4o">
                </div>
                <button type="button" class="btn btn-secondary" onclick="startExpressWithOptions()">Run quick install with these settings →</button>
            </div>
        </div>
        <script>
            function expressPresetChange() {
                var p = document.getElementById('express-preset').value;
                var t = document.getElementById('express-tenancy');
                var d = document.getElementById('express-demo');
                if (p === 'internal') { t.value = 'single'; d.value = 'none'; }
                else if (p === 'saas') { t.value = 'multi'; d.value = 'none'; }
                else if (p === 'ai_first') { t.value = 'multi'; d.value = 'minimal'; }
                toggleExpressSingleOrg();
            }
            function toggleExpressSingleOrg() {
                var wrap = document.getElementById('express-single-org-wrap');
                wrap.style.display = document.getElementById('express-tenancy').value === 'single' ? 'block' : 'none';
            }
            function toggleExpressAiFields() {
                var provider = document.getElementById('express-ai-provider').value;
                var needsKey = provider && provider !== 'ollama';
                document.getElementById('express-ai-key-wrap').style.display = needsKey ? 'block' : 'none';
                document.getElementById('express-ai-model-wrap').style.display = provider ? 'block' : 'none';
            }
            document.getElementById('express-ai-provider').addEventListener('change', toggleExpressAiFields);
            function startExpressWithOptions() {
                var opts = {
                    tenancy: document.getElementById('express-tenancy').value,
                    demo: document.getElementById('express-demo').value
                };
                var preset = document.getElementById('express-preset').value;
                if (preset) opts.preset = preset;
                if (opts.tenancy === 'single') {
                    var nameEl = document.getElementById('express-single-org-name');
                    if (nameEl && nameEl.value.trim()) opts.single_org_name = nameEl.value.trim();
                }
                var siteNameEl = document.getElementById('express-site-name');
                if (siteNameEl && siteNameEl.value.trim()) opts.site_name = siteNameEl.value.trim();
                var localeEl = document.getElementById('express-locale');
                if (localeEl && localeEl.value) opts.locale = localeEl.value;
                var fallbackEl = document.getElementById('express-fallback-locale');
                if (fallbackEl && fallbackEl.value) opts.fallback_locale = fallbackEl.value;
                var aiProvider = document.getElementById('express-ai-provider').value;
                if (aiProvider) {
                    opts.ai_provider = aiProvider;
                    if (aiProvider !== 'ollama') {
                        var keyEl = document.getElementById('express-ai-api-key');
                        if (keyEl && keyEl.value.trim()) opts.ai_api_key = keyEl.value.trim();
                    }
                    var modelEl = document.getElementById('express-ai-model');
                    if (modelEl && modelEl.value.trim()) opts.ai_model = modelEl.value.trim();
                }
                startExpressInstall(opts);
            }
        </script>

        @php $dbDriver = old('driver', 'pgsql'); @endphp
        <form method="POST" action="{{ route('install.store') }}" id="db-form">
            @csrf
            <input type="hidden" name="step" value="database">
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="driver" value="sqlite" @checked($dbDriver === 'sqlite') onchange="toggleDb(this)">
                    <div>
                        <div class="radio-label">SQLite</div>
                        <div class="radio-desc">File-based, zero configuration. Perfect for local and small deployments.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="driver" value="pgsql" @checked($dbDriver === 'pgsql') onchange="toggleDb(this)">
                    <div>
                        <div class="radio-label">PostgreSQL</div>
                        <div class="radio-desc">Recommended for production. Full-featured relational database.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="driver" value="mysql" @checked($dbDriver === 'mysql') onchange="toggleDb(this)">
                    <div>
                        <div class="radio-label">MySQL / MariaDB</div>
                        <div class="radio-desc">Widely supported relational database.</div>
                    </div>
                </label>
            </div>
            <div class="extra-fields" id="server-fields" style="display:{{ $dbDriver === 'sqlite' ? 'none' : 'block' }}">
                <div class="two-col">
                    <div class="field"><label>Host</label><input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}"></div>
                    <div class="field"><label>Port</label><input type="number" name="db_port" id="db-port" value="{{ old('db_port', '5432') }}"></div>
                </div>
                <div class="field"><label>Database name</label><input type="text" name="db_database" value="{{ old('db_database', 'laravel') }}" required></div>
                <div class="two-col">
                    <div class="field"><label>Username</label><input type="text" name="db_username" value="{{ old('db_username', 'root') }}"></div>
                    <div class="field"><label>Password</label><input type="password" name="db_password"></div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" data-test-connection data-step="database" data-form-id="db-form" data-result-id="test-result-database">Test connection</button>
            <div id="test-result-database" class="test-result" role="status" aria-live="polite"></div>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </form>
        <script>
            function toggleDb(r) {
                document.getElementById('server-fields').style.display = r.value === 'sqlite' ? 'none' : 'block';
                const p = document.getElementById('db-port');
                if (r.value === 'pgsql') p.value = '5432';
                if (r.value === 'mysql') p.value = '3306';
            }
            const c = document.querySelector('input[name="driver"]:checked');
            if (c && c.value !== 'sqlite') toggleDb(c);
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 2: Migrate --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'migrate')

        <h1>Create tables</h1>
        <p class="subtitle">This creates all database tables, seeds roles &amp; permissions, gamification levels, and email templates.</p>
        <div class="info-box" id="migrate-status-box">Database connected. Click Run setup to create tables and seed data.</div>
        <div class="field" style="margin-top:1rem">
            <button type="button" class="btn btn-primary" id="migrate-run-btn" data-migrate-run-url="{{ route('install.migrate.run') }}" data-migrate-status-url="{{ route('install.migrate.status') }}" data-csrf="{{ csrf_token() }}">Run setup →</button>
        </div>
        <script>
        (function() {
            var btn = document.getElementById('migrate-run-btn');
            var box = document.getElementById('migrate-status-box');
            if (!btn || !box) return;
            var runUrl = btn.getAttribute('data-migrate-run-url');
            var statusUrl = btn.getAttribute('data-migrate-status-url');
            btn.addEventListener('click', function() {
                btn.disabled = true;
                box.textContent = 'Starting…';
                fetch(runUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': btn.getAttribute('data-csrf'), 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: '{}' })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.error) { box.textContent = data.error; btn.disabled = false; return; }
                        var key = data.progress_key;
                        function poll() {
                            fetch(statusUrl + '?key=' + encodeURIComponent(key), { headers: { 'Accept': 'application/json' } })
                                .then(function(r) { return r.json(); })
                                .then(function(s) {
                                    box.textContent = s.message || 'Running…';
                                    if (s.error) { box.textContent = 'Error: ' + s.error; btn.disabled = false; return; }
                                    if (s.done) { box.textContent = 'Done. Redirecting…'; window.location.href = "{{ route('install') }}"; return; }
                                    setTimeout(poll, 400);
                                })
                                .catch(function() { box.textContent = 'Could not check status.'; setTimeout(poll, 1000); });
                        }
                        setTimeout(poll, 300);
                    })
                    .catch(function() { box.textContent = 'Failed to start setup.'; btn.disabled = false; });
            });
        })();
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 3: Admin --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'admin')

        <h1>Create admin account</h1>
        <p class="subtitle">The first super-admin account has full access to all settings, users, and Filament admin panels.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="admin">
            <div class="field"><label>Full name</label><input type="text" name="name" value="{{ old('name', 'Admin') }}" required autofocus></div>
            <div class="field"><label>Email address</label><input type="email" name="email" value="{{ old('email', 'superadmin@example.com') }}" required></div>
            <div class="two-col">
                <div class="field"><label>Password</label><input type="password" name="password" required><p class="hint">Min 8 characters</p></div>
                <div class="field"><label>Confirm password</label><input type="password" name="password_confirmation" required></div>
            </div>
            <button type="submit" class="btn btn-primary">Create account →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 4: App basics --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'app')

        <h1>App basics</h1>
        <p class="subtitle">Essential details used across the app, emails, and OAuth callbacks.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="app">
            <div class="field"><label>Application name</label><input type="text" name="site_name" value="{{ old('site_name', 'My App') }}" required autofocus></div>
            <div class="field"><label>Application URL</label><input type="url" name="url" value="{{ old('url', request()->root()) }}" required placeholder="https://example.com"><p class="hint">Used for emails, OAuth callbacks, and webhooks.</p></div>
            @php $timezone = old('timezone', 'UTC'); @endphp
            <div class="field">
                <label>Timezone</label>
                <select name="timezone">
                    @foreach (timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" @selected($tz === $timezone)>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            @php $locale = old('locale', 'en'); $fallback = old('fallback_locale', 'en'); @endphp
            <div class="field">
                <label>Install preset</label>
                <select name="preset">
                    <option value="none" @selected((old('preset', session('install_preset', 'none'))) === 'none')>None — configure each step manually</option>
                    <option value="saas" @selected((old('preset', session('install_preset'))) === 'saas')>SaaS — multi-tenant, billing, optional AI</option>
                    <option value="internal" @selected((old('preset', session('install_preset'))) === 'internal')>Internal tool — single-tenant, no billing</option>
                    <option value="ai_first" @selected((old('preset', session('install_preset'))) === 'ai_first')>AI-first — multi-tenant, AI enabled</option>
                </select>
                <p class="hint">Presets prefill later steps; you can still change any value.</p>
            </div>
            <div class="two-col">
                <div class="field">
                    <label>Locale</label>
                    <select name="locale">
                        <option value="en" @selected($locale === 'en')>English</option>
                        <option value="es" @selected($locale === 'es')>Spanish</option>
                        <option value="fr" @selected($locale === 'fr')>French</option>
                        <option value="de" @selected($locale === 'de')>German</option>
                        <option value="pt" @selected($locale === 'pt')>Portuguese</option>
                        <option value="it" @selected($locale === 'it')>Italian</option>
                        <option value="nl" @selected($locale === 'nl')>Dutch</option>
                        <option value="ja" @selected($locale === 'ja')>Japanese</option>
                        <option value="ko" @selected($locale === 'ko')>Korean</option>
                        <option value="zh" @selected($locale === 'zh')>Chinese</option>
                        <option value="ar" @selected($locale === 'ar')>Arabic</option>
                    </select>
                </div>
                <div class="field">
                    <label>Fallback locale</label>
                    <select name="fallback_locale">
                        <option value="en" @selected($fallback === 'en')>English</option>
                        <option value="es" @selected($fallback === 'es')>Spanish</option>
                        <option value="fr" @selected($fallback === 'fr')>French</option>
                        <option value="de" @selected($fallback === 'de')>German</option>
                        <option value="pt" @selected($fallback === 'pt')>Portuguese</option>
                        <option value="it" @selected($fallback === 'it')>Italian</option>
                        <option value="nl" @selected($fallback === 'nl')>Dutch</option>
                        <option value="ja" @selected($fallback === 'ja')>Japanese</option>
                        <option value="ko" @selected($fallback === 'ko')>Korean</option>
                        <option value="zh" @selected($fallback === 'zh')>Chinese</option>
                        <option value="ar" @selected($fallback === 'ar')>Arabic</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 5: Tenancy --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'tenancy')

        <h1>Tenancy mode <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Choose how your app handles organizations. Single-organization hides org UI; change later in Settings → Tenancy.</p>
        <form method="POST" action="{{ route('install.store') }}" id="tenancy-form">
            @csrf
            <input type="hidden" name="step" value="tenancy">
            @php $preset = session('install_preset', 'none'); $tenancyEnabled = old('enabled', $preset === 'internal' ? '0' : '1'); @endphp
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="enabled" value="1" @checked($tenancyEnabled === '1') onchange="toggleTenancyMode(this)">
                    <div>
                        <div class="radio-label">Multi-organization</div>
                        <div class="radio-desc">Users can create and belong to multiple organizations. Ideal for SaaS, teams, and B2B apps.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="enabled" value="0" @checked($tenancyEnabled === '0') onchange="toggleTenancyMode(this)">
                    <div>
                        <div class="radio-label">Single-organization</div>
                        <div class="radio-desc">One organization for all users. Org switcher and management are hidden. Ideal for internal tools.</div>
                    </div>
                </label>
            </div>
            <div id="tenancy-multi-options">
                <div class="field"><label>Default personal workspace name</label><input type="text" name="default_org_name" value="{{ old('default_org_name', "{name}'s Workspace") }}" placeholder="{name}'s Workspace"><p class="hint">Use {name} for the user's name.</p></div>
                <div class="toggle-row">
                    <div><div class="toggle-label">Users can create organizations</div><div class="radio-desc">Allow non-admin users to create their own orgs.</div></div>
                    <div class="toggle-wrap"><input type="checkbox" name="allow_user_org_creation" value="1" checked></div>
                </div>
                <div class="toggle-row" style="padding-top:0.625rem">
                    <div><div class="toggle-label">Auto-create personal workspace (for org admins)</div><div class="radio-desc">Users who register or are added as admins get a personal org.</div></div>
                    <div class="toggle-wrap"><input type="checkbox" name="auto_create_personal_org_for_admins" value="1" checked></div>
                </div>
                <div class="toggle-row" style="padding-top:0.625rem">
                    <div><div class="toggle-label">Auto-create personal workspace (for org members)</div><div class="radio-desc">Users who join only as members (e.g. via invite) get a personal org.</div></div>
                    <div class="toggle-wrap"><input type="checkbox" name="auto_create_personal_org_for_members" value="1"></div>
                </div>
                <div class="section-label">Advanced tenancy</div>
                <div class="field"><label>Primary domain <span style="color:#525252">(optional)</span></label><input type="text" name="domain" value="{{ old('domain') }}" placeholder="app.example.com"><p class="hint">Used for subdomain resolution; leave empty if not using custom domains yet.</p></div>
                <div class="toggle-row">
                    <div><div class="toggle-label">Subdomain resolution</div><div class="radio-desc">Resolve tenant from subdomain (e.g. acme.app.com).</div></div>
                    <div class="toggle-wrap"><input type="checkbox" name="subdomain_resolution" value="1" checked></div>
                </div>
                <div class="two-col">
                    <div class="field"><label>Singular term</label><input type="text" name="term" value="{{ old('term', 'Organization') }}"></div>
                    <div class="field"><label>Plural term</label><input type="text" name="term_plural" value="{{ old('term_plural', 'Organizations') }}"></div>
                </div>
                <div class="two-col">
                    <div class="field"><label>Invitation expires (days)</label><input type="number" name="invitation_expires_in_days" value="{{ old('invitation_expires_in_days', 7) }}" min="1" max="90"></div>
                    <div class="toggle-row" style="border:none;padding-top:0">
                        <div><div class="toggle-label">Allow registration via invite</div></div>
                        <div class="toggle-wrap"><input type="checkbox" name="invitation_allow_registration" value="1" checked></div>
                    </div>
                </div>
                <div class="field">
                    <label>Sharing: edit ownership</label>
                    <select name="sharing_edit_ownership">
                        <option value="original_owner" @selected(old('sharing_edit_ownership', 'original_owner') === 'original_owner')>Original owner</option>
                        <option value="current_owner" @selected(old('sharing_edit_ownership') === 'current_owner')>Current owner</option>
                    </select>
                </div>
                <div class="toggle-row">
                    <div><div class="toggle-label">Restrict sharing to connected users only</div></div>
                    <div class="toggle-wrap"><input type="checkbox" name="sharing_restrict_to_connected" value="1"></div>
                </div>
                <div class="toggle-row">
                    <div><div class="toggle-label">Super-admin can view all orgs</div></div>
                    <div class="toggle-wrap"><input type="checkbox" name="super_admin_can_view_all" value="1" checked></div>
                </div>
                <div class="toggle-row">
                    <div><div class="toggle-label">Default new content shared to all orgs (super-admin)</div></div>
                    <div class="toggle-wrap"><input type="checkbox" name="super_admin_default_share_new_to_all_orgs" value="1" checked></div>
                </div>
            </div>
            <div id="tenancy-single-options" style="display:none">
                <div class="field"><label>Organization name</label><input type="text" name="single_org_name" value="{{ old('single_org_name') }}" placeholder="My Company"><p class="hint">The single workspace name shown in the app.</p></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="tenancy">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — keep defaults →</button>
        </form>
        <script>
            function toggleTenancyMode(radio) {
                var multi = document.getElementById('tenancy-multi-options');
                var single = document.getElementById('tenancy-single-options');
                if (radio.value === '1') { multi.style.display = 'block'; single.style.display = 'none'; } else { multi.style.display = 'none'; single.style.display = 'block'; }
            }
            (function() {
                var r = document.querySelector('#tenancy-form input[name="enabled"]:checked');
                if (r) toggleTenancyMode(r);
            })();
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 6: Infrastructure --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'infrastructure')

        <h1>Cache, sessions &amp; queue <span class="badge-opt">optional</span></h1>
        <p class="subtitle">The "database" driver works out of the box. Upgrade to Redis for better performance in production.</p>
        <form method="POST" action="{{ route('install.store') }}" id="infra-form">
            @csrf
            <input type="hidden" name="step" value="infrastructure">
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="driver" value="database" checked onchange="toggleInfra(this)">
                    <div>
                        <div class="radio-label">Database (default)</div>
                        <div class="radio-desc">Cache, sessions, and jobs stored in your database. Zero extra setup.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="driver" value="redis" onchange="toggleInfra(this)">
                    <div>
                        <div class="radio-label">Redis</div>
                        <div class="radio-desc">Recommended for production. Faster, reduces DB load. Required for Horizon &amp; WebSockets.</div>
                    </div>
                </label>
            </div>
            <div class="extra-fields" id="redis-fields" style="display:none">
                <div class="two-col">
                    <div class="field"><label>Redis host</label><input type="text" name="redis_host" value="{{ old('redis_host', '127.0.0.1') }}"></div>
                    <div class="field"><label>Redis port</label><input type="number" name="redis_port" value="{{ old('redis_port', '6379') }}"></div>
                </div>
                <div class="field"><label>Redis password <span style="color:#525252">(leave blank if none)</span></label><input type="password" name="redis_password"></div>
                <button type="button" class="btn btn-secondary" data-test-connection data-step="infrastructure" data-form-id="infra-form" data-result-id="test-result-infrastructure">Test connection</button>
                <div id="test-result-infrastructure" class="test-result" role="status" aria-live="polite"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="infrastructure">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — use database driver →</button>
        </form>
        <script>
            function toggleInfra(r) {
                document.getElementById('redis-fields').style.display = r.value === 'redis' ? 'block' : 'none';
            }
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 7: Mail --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'mail')

        <h1>Mail <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Configure how your app sends email. "Log" is fine for local development. Configure a real provider before going live.</p>
        <form method="POST" action="{{ route('install.store') }}" id="mail-form">
            @csrf
            <input type="hidden" name="step" value="mail">
            @php
                $mailer = old('mailer', 'smtp');
                $smtpHost = old('smtp_host', '127.0.0.1');
                $smtpPort = old('smtp_port', '2525');
                $smtpUsername = old('smtp_username', config('app.name'));
                $smtpEncryption = old('smtp_encryption', '');
            @endphp
            <div class="field">
                <label>Mail driver</label>
                <select name="mailer" onchange="toggleMail(this.value)">
                    <option value="log" @selected($mailer === 'log')>Log (development only)</option>
                    <option value="smtp" @selected($mailer === 'smtp')>SMTP</option>
                    <option value="ses">Amazon SES</option>
                    <option value="postmark">Postmark</option>
                    <option value="resend">Resend</option>
                    <option value="mailgun">Mailgun</option>
                </select>
            </div>
            <div id="smtp-fields" style="display:{{ $mailer === 'smtp' ? 'block' : 'none' }}">
                <div class="two-col">
                    <div class="field"><label>SMTP host</label><input type="text" name="smtp_host" value="{{ $smtpHost }}" placeholder="127.0.0.1"></div>
                    <div class="field"><label>SMTP port</label><input type="number" name="smtp_port" value="{{ $smtpPort }}" placeholder="2525"></div>
                </div>
                <div class="two-col">
                    <div class="field"><label>Username</label><input type="text" name="smtp_username" value="{{ $smtpUsername }}" placeholder="{{ config('app.name') }}"></div>
                    <div class="field"><label>Password</label><input type="password" name="smtp_password" placeholder="Optional for Herd"></div>
                </div>
                <div class="field">
                    <label>Encryption</label>
                    <select name="smtp_encryption">
                        <option value="tls" @selected($smtpEncryption === 'tls')>TLS (port 587)</option>
                        <option value="ssl" @selected($smtpEncryption === 'ssl')>SSL (port 465)</option>
                        <option value="" @selected($smtpEncryption === '')>None (Herd / port 2525)</option>
                    </select>
                </div>
                <button type="button" class="btn btn-secondary" data-test-connection data-step="mail" data-form-id="mail-form" data-result-id="test-result-mail">Test connection</button>
                <div id="test-result-mail" class="test-result" role="status" aria-live="polite"></div>
            </div>
            <div class="section-label">From address</div>
            <div class="two-col">
                <div class="field"><label>From email</label><input type="email" name="from_address" value="{{ old('from_address', 'hello@example.com') }}" placeholder="hello@example.com"></div>
                <div class="field"><label>From name</label><input type="text" name="from_name" value="{{ old('from_name', config('app.name', 'My App')) }}"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="mail">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — use log driver for now →</button>
        </form>
        <script>
            function toggleMail(v) {
                document.getElementById('smtp-fields').style.display = v === 'smtp' ? 'block' : 'none';
            }
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 8: Search --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'search')

        <h1>Full-text search <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Laravel Scout powers search. "Collection" works without setup. Upgrade to Typesense for production-grade relevance.</p>
        <form method="POST" action="{{ route('install.store') }}" id="search-form">
            @csrf
            <input type="hidden" name="step" value="search">
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="driver" value="collection" checked onchange="toggleSearch(this.value)">
                    <div>
                        <div class="radio-label">Collection (default)</div>
                        <div class="radio-desc">In-memory search over Eloquent collections. No setup, no extra services.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="driver" value="typesense" onchange="toggleSearch(this.value)">
                    <div>
                        <div class="radio-label">Typesense</div>
                        <div class="radio-desc">Production-grade, typo-tolerant, fast. Run locally with Herd or Docker.</div>
                    </div>
                </label>
            </div>
            <div id="typesense-fields" style="display:none">
                <div class="two-col">
                    <div class="field"><label>Host</label><input type="text" name="typesense_host" value="{{ old('typesense_host', 'localhost') }}"></div>
                    <div class="field"><label>Port</label><input type="number" name="typesense_port" value="{{ old('typesense_port', '8108') }}"></div>
                </div>
                <div class="field"><label>API key</label><input type="text" name="typesense_api_key" value="{{ old('typesense_api_key') }}" placeholder="LARAVEL-HERD or your key"></div>
                @php $typesenseProtocol = old('typesense_protocol', 'http'); @endphp
                <div class="field">
                    <label>Protocol</label>
                    <select name="typesense_protocol">
                        <option value="http" @selected($typesenseProtocol === 'http')>HTTP</option>
                        <option value="https" @selected($typesenseProtocol === 'https')>HTTPS</option>
                    </select>
                </div>
                <button type="button" class="btn btn-secondary" data-test-connection data-step="search" data-form-id="search-form" data-result-id="test-result-search">Test connection</button>
                <div id="test-result-search" class="test-result" role="status" aria-live="polite"></div>
            </div>
            <div class="section-label">Scout options</div>
            <div class="field"><label>Index prefix</label><input type="text" name="prefix" value="{{ old('prefix') }}" placeholder="Leave empty for default"></div>
            <div class="toggle-row" style="border:none">
                <div><div class="toggle-label">Queue indexing</div><div class="radio-desc">Index changes in the queue (recommended for production).</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="queue" value="1"></div>
            </div>
            <div class="toggle-row">
                <div><div class="toggle-label">Identify models when indexing</div><div class="radio-desc">Include model class in indexed records.</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="identify" value="1"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="search">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — keep collection driver →</button>
        </form>
        <script>
            function toggleSearch(v) {
                document.getElementById('typesense-fields').style.display = v === 'typesense' ? 'block' : 'none';
            }
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 9: AI providers --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'ai')

        <h1>AI providers <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Configure your default LLM provider. More providers can be added later in Settings → AI / Prism.</p>
        <form method="POST" action="{{ route('install.store') }}" id="ai-form">
            @csrf
            <input type="hidden" name="step" value="ai">
            @php $aiProvider = old('provider', 'openrouter'); @endphp
            <div class="field">
                <label>Default provider</label>
                <select name="provider" onchange="toggleAi(this.value)">
                    <option value="" @selected($aiProvider === '')>None / skip</option>
                    <option value="openrouter" @selected($aiProvider === 'openrouter')>OpenRouter (free models available)</option>
                    <option value="openai" @selected($aiProvider === 'openai')>OpenAI</option>
                    <option value="anthropic" @selected($aiProvider === 'anthropic')>Anthropic (Claude)</option>
                    <option value="groq" @selected($aiProvider === 'groq')>Groq</option>
                    <option value="gemini" @selected($aiProvider === 'gemini')>Google Gemini</option>
                    <option value="xai" @selected($aiProvider === 'xai')>xAI (Grok)</option>
                    <option value="deepseek" @selected($aiProvider === 'deepseek')>DeepSeek</option>
                    <option value="mistral" @selected($aiProvider === 'mistral')>Mistral</option>
                    <option value="ollama" @selected($aiProvider === 'ollama')>Ollama (local, no key needed)</option>
                </select>
            </div>
            <div id="ai-key-field">
                <div class="field"><label>API key</label><input type="password" name="api_key" placeholder="sk-..."><p class="hint">Leave blank for Ollama.</p></div>
                <div class="field">
                    <label for="ai-model-input">Default model <span style="color:#525252">(optional)</span></label>
                    <div class="ai-model-combobox" id="ai-model-combobox">
                        <input type="text" id="ai-model-input" name="model" value="{{ old('model', '') }}" placeholder="Type to search or select a model" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="ai-model-listbox" aria-autocomplete="list" aria-label="Default model">
                        <div class="ai-model-list" id="ai-model-listbox" role="listbox" aria-label="Model options" style="display:none;"></div>
                    </div>
                    <p class="hint">Select from the list (free and paid) or enter a model ID. With OpenRouter + API key, <button type="button" class="btn-link" id="ai-model-load-live" style="padding:0;vertical-align:baseline;">load full list</button>.</p>
                </div>
            </div>
            <div class="field">
                <label>Cohere API key <span style="color:#525252">(optional, for reranking)</span></label>
                <input type="password" name="cohere_api_key" value="{{ old('cohere_api_key') }}" placeholder="Used for Laravel AI SDK reranking">
                <p class="hint">Get a key at <a href="https://dashboard.cohere.com/api-keys" target="_blank" rel="noopener">dashboard.cohere.com</a>. Stored in Settings → AI and in .env as <code>COHERE_API_KEY</code>.</p>
            </div>
            <div class="field">
                <label>Jina API key <span style="color:#525252">(optional, reranking alternative)</span></label>
                <input type="password" name="jina_api_key" value="{{ old('jina_api_key') }}" placeholder="Alternative to Cohere for reranking">
                <p class="hint">Stored encrypted in Settings → AI; <code>JINA_API_KEY</code> written to .env when set.</p>
            </div>
            <div class="field">
                <label>Thesys C1 API key <span style="color:#525252">(optional)</span></label>
                <input type="password" name="thesys_api_key" value="{{ old('thesys_api_key') }}" placeholder="Used for DataTable Visualize and other Thesys features">
                <p class="hint">Get a key at <a href="https://www.thesys.dev" target="_blank" rel="noopener">thesys.dev</a>. Skip to disable; add later in .env as <code>THESYS_API_KEY</code>.</p>
            </div>
            <div class="field" style="margin-top:1rem; display:flex; flex-wrap:wrap; gap:0.75rem; align-items:flex-start;">
                <div>
                    <button type="button" class="btn btn-secondary" data-test-connection data-step="ai" data-form-id="ai-form" data-result-id="test-result-ai" data-ai-test="provider">Test provider API key</button>
                    <div id="test-result-ai" class="test-result" role="status" aria-live="polite"></div>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-test-connection data-step="ai" data-form-id="ai-form" data-result-id="test-result-cohere" data-ai-test="cohere">Test Cohere key</button>
                    <div id="test-result-cohere" class="test-result" role="status" aria-live="polite"></div>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-test-connection data-step="ai" data-form-id="ai-form" data-result-id="test-result-thesys" data-ai-test="thesys">Test Thesys key</button>
                    <div id="test-result-thesys" class="test-result" role="status" aria-live="polite"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="ai">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — configure AI later →</button>
        </form>
        <script>
            function toggleAi(v) {
                document.getElementById('ai-key-field').style.display = (v === '' || v === 'ollama') ? 'none' : 'block';
            }

            (function() {
                var modelsUrl = '{{ route("install.ai-models") }}';
                var token = document.querySelector('#ai-form input[name="_token"]');
                var input = document.getElementById('ai-model-input');
                var listbox = document.getElementById('ai-model-listbox');
                var loadLiveBtn = document.getElementById('ai-model-load-live');
                var allModels = [];
                var filteredModels = [];
                var selectedIndex = -1;
                var loaded = false;

                function renderList(models) {
                    filteredModels = models;
                    selectedIndex = -1;
                    listbox.innerHTML = '';
                    if (models.length === 0) {
                        listbox.classList.add('visible');
                        listbox.style.display = 'block';
                        listbox.innerHTML = '<div class="ai-model-list-loading">No models match.</div>';
                        return;
                    }
                    models.forEach(function(m, i) {
                        var opt = document.createElement('button');
                        opt.type = 'button';
                        opt.className = 'ai-model-option';
                        opt.role = 'option';
                        opt.setAttribute('aria-selected', 'false');
                        opt.setAttribute('data-id', m.id);
                        opt.setAttribute('data-index', String(i));
                        var nameSpan = document.createElement('span');
                        nameSpan.className = 'ai-model-name';
                        nameSpan.textContent = m.name;
                        opt.appendChild(nameSpan);
                        var priceSpan = document.createElement('span');
                        priceSpan.className = m.free ? 'ai-model-free' : 'ai-model-pricing';
                        priceSpan.textContent = m.free ? 'Free' : m.pricing;
                        opt.appendChild(priceSpan);
                        opt.addEventListener('click', function() { selectModel(m.id); });
                        listbox.appendChild(opt);
                    });
                    listbox.classList.add('visible');
                    listbox.style.display = 'block';
                    input.setAttribute('aria-expanded', 'true');
                }

                function filterModels(q) {
                    q = (q || '').toLowerCase().trim();
                    if (q === '') return allModels;
                    return allModels.filter(function(m) {
                        return m.id.toLowerCase().indexOf(q) >= 0 || (m.name && m.name.toLowerCase().indexOf(q) >= 0);
                    });
                }

                function selectModel(id) {
                    input.value = id;
                    closeList();
                }

                function closeList() {
                    listbox.classList.remove('visible');
                    listbox.style.display = 'none';
                    listbox.innerHTML = '';
                    input.setAttribute('aria-expanded', 'false');
                    selectedIndex = -1;
                }

                function fetchModels(usePost) {
                    if (usePost && token) {
                        var fd = new FormData(document.getElementById('ai-form'));
                        fd.set('_token', token.value);
                        return fetch(modelsUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    }
                    return fetch(modelsUrl, { headers: { 'Accept': 'application/json' } });
                }

                function loadModels(usePost) {
                    if (listbox.innerHTML === '' && !loaded) listbox.innerHTML = '<div class="ai-model-list-loading">Loading models…</div>';
                    listbox.classList.add('visible');
                    listbox.style.display = 'block';
                    fetchModels(usePost)
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            loaded = true;
                            allModels = data.models || [];
                            renderList(filterModels(input.value));
                        })
                        .catch(function() {
                            listbox.innerHTML = '<div class="ai-model-list-loading">Could not load models.</div>';
                        });
                }

                input.addEventListener('focus', function() {
                    if (!loaded) loadModels(false);
                    else renderList(filterModels(input.value));
                });
                input.addEventListener('input', function() {
                    if (loaded) renderList(filterModels(input.value));
                });
                input.addEventListener('keydown', function(e) {
                    if (listbox.style.display !== 'block') return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, filteredModels.length - 1);
                        updateHighlight();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, -1);
                        updateHighlight();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (selectedIndex >= 0 && filteredModels[selectedIndex]) selectModel(filteredModels[selectedIndex].id);
                        else if (filteredModels.length === 1) selectModel(filteredModels[0].id);
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        closeList();
                    }
                });

                function updateHighlight() {
                    var opts = listbox.querySelectorAll('.ai-model-option');
                    opts.forEach(function(o, i) {
                        o.setAttribute('aria-selected', i === selectedIndex ? 'true' : 'false');
                    });
                    if (selectedIndex >= 0 && opts[selectedIndex]) opts[selectedIndex].scrollIntoView({ block: 'nearest' });
                }

                document.addEventListener('click', function(e) {
                    var cb = document.getElementById('ai-model-combobox');
                    if (cb && !cb.contains(e.target)) closeList();
                });

                if (loadLiveBtn) loadLiveBtn.addEventListener('click', function() {
                    loaded = false;
                    loadModels(true);
                });
            })();
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 10: Social auth --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'social')

        <h1>Social login <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Enable Google and/or GitHub OAuth. Register at <strong>console.cloud.google.com</strong> and <strong>github.com/settings/apps</strong>.</p>
        <div class="info-box">Redirect URL: <code style="background:#0f0f0f;border-radius:4px;padding:1px 6px">{{ url('/auth/{provider}/callback') }}</code></div>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="social">
            <div class="section-label">Google</div>
            <div class="two-col">
                <div class="field"><label>Client ID</label><input type="text" name="google_client_id" value="{{ old('google_client_id') }}" placeholder="...apps.googleusercontent.com"></div>
                <div class="field"><label>Client Secret</label><input type="password" name="google_client_secret"></div>
            </div>
            <div class="section-label">GitHub</div>
            <div class="two-col">
                <div class="field"><label>Client ID</label><input type="text" name="github_client_id" value="{{ old('github_client_id') }}" placeholder="Ov23li..."></div>
                <div class="field"><label>Client Secret</label><input type="password" name="github_client_secret"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="social">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip for now →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 11: Storage --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'storage')

        <h1>File storage <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Choose where uploaded files are stored. Local disk is fine to start.</p>
        <form method="POST" action="{{ route('install.store') }}" id="storage-form">
            @csrf
            <input type="hidden" name="step" value="storage">
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="disk" value="local" checked onchange="toggleStorage(this)">
                    <div><div class="radio-label">Local disk</div><div class="radio-desc">Files stored on this server.</div></div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="disk" value="s3" onchange="toggleStorage(this)">
                    <div><div class="radio-label">S3-compatible</div><div class="radio-desc">Amazon S3, Cloudflare R2, DigitalOcean Spaces, etc.</div></div>
                </label>
            </div>
            <div class="extra-fields" id="s3-fields" style="display:none">
                <div class="two-col">
                    <div class="field"><label>Access key ID</label><input type="text" name="s3_key"></div>
                    <div class="field"><label>Secret access key</label><input type="password" name="s3_secret"></div>
                </div>
                <div class="two-col">
                    <div class="field"><label>Region</label><input type="text" name="s3_region" value="{{ old('s3_region', 'us-east-1') }}"></div>
                    <div class="field"><label>Bucket name</label><input type="text" name="s3_bucket"></div>
                </div>
                <div class="field"><label>Custom endpoint URL <span style="color:#525252">(blank for AWS)</span></label><input type="url" name="s3_url" placeholder="https://...r2.cloudflarestorage.com"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="storage">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip for now →</button>
        </form>
        <script>
            function toggleStorage(r) {
                document.getElementById('s3-fields').style.display = r.value === 's3' ? 'block' : 'none';
            }
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 12: Broadcasting --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'broadcasting')

        <h1>Broadcasting <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Real-time WebSockets via Laravel Reverb. Needed for live notifications, chat, and collaborative features.</p>
        <form method="POST" action="{{ route('install.store') }}" id="broadcasting-form">
            @csrf
            <input type="hidden" name="step" value="broadcasting">
            <div class="info-box">Pre-filled with <strong>Laravel Herd</strong> Reverb defaults. <a href="http://reverb-dashboard.herd.test/" target="_blank" rel="noopener" style="color:#93c5fd;">Dashboard → reverb-dashboard.herd.test</a>. Or run <code style="background:#0f0f0f;border-radius:4px;padding:1px 6px;font-size:0.8em">php artisan reverb:install</code> and paste your own credentials.</div>
            <div class="two-col">
                <div class="field"><label>App ID</label><input type="text" name="reverb_app_id" value="{{ old('reverb_app_id', '1001') }}"></div>
                <div class="field"><label>App Key</label><input type="text" name="reverb_app_key" value="{{ old('reverb_app_key', 'laravel-herd') }}"></div>
            </div>
            <div class="field"><label>App Secret</label><input type="password" name="reverb_app_secret" placeholder="Herd default: secret" value="{{ old('reverb_app_secret') }}"></div>
            <div class="two-col">
                <div class="field"><label>Host</label><input type="text" name="reverb_host" value="{{ old('reverb_host', 'reverb.herd.test') }}"></div>
                <div class="field"><label>Port</label><input type="number" name="reverb_port" value="{{ old('reverb_port', '443') }}"></div>
            </div>
            @php $reverbScheme = old('reverb_scheme', 'https'); @endphp
            <div class="field">
                <label>Scheme</label>
                <select name="reverb_scheme">
                    <option value="http" @selected($reverbScheme === 'http')>http (local)</option>
                    <option value="https" @selected($reverbScheme === 'https')>https (Herd / production)</option>
                </select>
            </div>
            <div class="field" style="margin-top:1rem">
                <button type="button" class="btn btn-secondary" data-test-connection data-step="broadcasting" data-form-id="broadcasting-form" data-result-id="test-result-broadcasting">Test broadcasting</button>
                <div id="test-result-broadcasting" class="test-result" role="status" aria-live="polite"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="broadcasting">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip for now →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 13: SEO --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'seo')

        <h1>SEO <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Default meta tags for search engines and social sharing. Editable later in Settings → SEO.</p>
        @php $appName = config('app.name', 'My App'); @endphp
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="seo">
            <div class="field"><label>Page title</label><input type="text" name="meta_title" value="{{ old('meta_title', $appName) }}" maxlength="70"><p class="hint">Recommended: ≤ 60 characters</p></div>
            <div class="field">
                <label>Meta description</label>
                <textarea name="meta_description" rows="3" maxlength="160" style="width:100%;background:#0f0f0f;border:1px solid #2a2a2a;border-radius:8px;padding:0.625rem 0.75rem;font-size:0.875rem;color:#e5e5e5;resize:vertical;font-family:inherit">{{ old('meta_description', '') }}</textarea>
                <p class="hint">Recommended: ≤ 160 characters</p>
            </div>
            <div class="field"><label>Open Graph image URL <span style="color:#525252">(optional)</span></label><input type="url" name="og_image" value="{{ old('og_image') }}" placeholder="https://example.com/og-image.png"><p class="hint">Ideal: 1200×630 px</p></div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="seo">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip for now →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 14: Monitoring --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'monitoring')

        <h1>Error tracking <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Connect Sentry to capture errors and performance data. Get a free DSN at <strong>sentry.io</strong>.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="monitoring">
            <div class="field"><label>Sentry DSN</label><input type="text" name="sentry_dsn" value="{{ old('sentry_dsn') }}" placeholder="https://...@sentry.io/..."></div>
            @php $sentryRate = old('sentry_sample_rate', '1.0'); @endphp
            <div class="field">
                <label>Error sample rate</label>
                <select name="sentry_sample_rate">
                    <option value="1.0" @selected($sentryRate === '1.0' || $sentryRate === 1.0)>1.0 — capture all errors</option>
                    <option value="0.5" @selected($sentryRate === '0.5' || $sentryRate === 0.5)>0.5 — capture 50%</option>
                    <option value="0.1" @selected($sentryRate === '0.1' || $sentryRate === 0.1)>0.1 — capture 10% (high-traffic)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="monitoring">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip for now →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step: Billing --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'billing')

        <h1>Billing <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Gateway, currency, trials, and API credentials. Same as Settings → Billing / Stripe / Paddle / Lemon Squeezy.</p>
        @php
            $preset = session('install_preset', 'none');
            $gateway = old('default_gateway', $preset === 'internal' ? 'none' : 'stripe');
            $currency = old('currency', 'usd');
            $trialDays = old('trial_days', 14);
        @endphp
        @if ($preset === 'internal')
            <div class="info-box">Internal tool preset: consider skipping billing or choose None (no payment gateway).</div>
        @endif
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="billing">
            <div class="field">
                <label>Default gateway</label>
                <select name="default_gateway">
                    <option value="none" @selected($gateway === 'none')>None (free app)</option>
                    <option value="stripe" @selected($gateway === 'stripe')>Stripe</option>
                    <option value="paddle" @selected($gateway === 'paddle')>Paddle</option>
                    <option value="lemon_squeezy" @selected($gateway === 'lemon_squeezy')>Lemon Squeezy</option>
                </select>
            </div>
            <div class="two-col">
                <div class="field">
                    <label>Currency</label>
                    <select name="currency">
                        <option value="usd" @selected($currency === 'usd')>USD</option>
                        <option value="eur" @selected($currency === 'eur')>EUR</option>
                        <option value="gbp" @selected($currency === 'gbp')>GBP</option>
                    </select>
                </div>
                <div class="field">
                    <label>Trial days</label>
                    <input type="number" name="trial_days" value="{{ $trialDays }}" min="0" max="365">
                </div>
            </div>
            <div class="two-col">
                <div class="field">
                    <label>Credit expiration (days)</label>
                    <input type="number" name="credit_expiration_days" value="{{ old('credit_expiration_days', 365) }}" min="0">
                </div>
                <div class="field">
                    <label>Dunning intervals (comma-separated days)</label>
                    <input type="text" name="dunning_intervals" value="{{ old('dunning_intervals', '3, 7, 14') }}" placeholder="3, 7, 14">
                </div>
            </div>
            <div class="toggle-row">
                <div><div class="toggle-label">Seat-based billing</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="enable_seat_based_billing" value="1"></div>
            </div>
            <div class="toggle-row">
                <div><div class="toggle-label">Allow multiple subscriptions per org</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="allow_multiple_subscriptions" value="1"></div>
            </div>
            <div class="toggle-row">
                <div><div class="toggle-label">Geo restriction</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="geo_restriction_enabled" value="1"></div>
            </div>
            <div class="field"><label>Blocked country codes (comma, ISO)</label><input type="text" name="geo_blocked_countries" value="{{ old('geo_blocked_countries') }}" placeholder="RU, BY"></div>
            <div class="field"><label>Allowed country codes (comma; if set, only these)</label><input type="text" name="geo_allowed_countries" value="{{ old('geo_allowed_countries') }}" placeholder="US, CA, GB"></div>
            <div class="section-label">Stripe</div>
            <div class="field"><label>Publishable key</label><input type="password" name="stripe_key" placeholder="pk_..."></div>
            <div class="field"><label>Secret key</label><input type="password" name="stripe_secret" placeholder="sk_..."></div>
            <div class="field"><label>Webhook signing secret</label><input type="password" name="stripe_webhook_secret" placeholder="whsec_..."></div>
            <div class="section-label">Paddle</div>
            <div class="field"><label>Vendor ID</label><input type="text" name="paddle_vendor_id"></div>
            <div class="field"><label>Vendor auth code</label><input type="password" name="paddle_vendor_auth_code"></div>
            <div class="field"><label>Public key</label><input type="text" name="paddle_public_key"></div>
            <div class="field"><label>Webhook secret</label><input type="password" name="paddle_webhook_secret"></div>
            <div class="toggle-row">
                <div><div class="toggle-label">Paddle sandbox</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="paddle_sandbox" value="1" checked></div>
            </div>
            <div class="section-label">Lemon Squeezy</div>
            <div class="field"><label>API key</label><input type="password" name="lemon_squeezy_api_key"></div>
            <div class="field"><label>Signing secret</label><input type="password" name="lemon_squeezy_signing_secret"></div>
            <div class="two-col">
                <div class="field"><label>Store ID</label><input type="text" name="lemon_squeezy_store"></div>
                <div class="field"><label>Path</label><input type="text" name="lemon_squeezy_path" value="{{ old('lemon_squeezy_path', 'lemon-squeezy') }}"></div>
            </div>
            <div class="two-col">
                <div class="field"><label>Currency locale</label><input type="text" name="lemon_squeezy_currency_locale" value="{{ old('lemon_squeezy_currency_locale', 'en') }}"></div>
                <div class="field"><label>Generic variant ID</label><input type="text" name="lemon_squeezy_generic_variant_id" placeholder="Optional"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="billing">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — keep defaults →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Integrations (Slack, Postmark, Resend) --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'integrations')

        <h1>Integrations <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Slack notifications and transactional mail providers. Same as Settings → Integrations.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="integrations">
            <div class="section-label">Slack</div>
            <div class="field"><label>Webhook URL</label><input type="password" name="slack_webhook_url" placeholder="https://hooks.slack.com/..."></div>
            <div class="field"><label>Bot token</label><input type="password" name="slack_bot_token"></div>
            <div class="field"><label>Channel</label><input type="text" name="slack_channel" placeholder="#alerts"></div>
            <div class="section-label">Postmark &amp; Resend</div>
            <div class="field"><label>Postmark token</label><input type="password" name="postmark_token"></div>
            <div class="field"><label>Resend API key</label><input type="password" name="resend_key"></div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="integrations">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — configure later →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Theme --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'theme')

        <h1>Theme &amp; appearance <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Defaults for the app shell and user-facing appearance. Same as Settings → Theme.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="theme">
            <div class="two-col">
                <div class="field"><label>Preset</label>
                    <select name="preset">
                        <option value="default" @selected(old('preset', 'default') === 'default')>Default</option>
                        <option value="saas" @selected(old('preset') === 'saas')>SaaS</option>
                        <option value="minimal" @selected(old('preset') === 'minimal')>Minimal</option>
                    </select>
                </div>
                <div class="field"><label>Base color</label>
                    <select name="base_color">
                        <option value="neutral" @selected(old('base_color', 'neutral') === 'neutral')>Neutral</option>
                        <option value="zinc" @selected(old('base_color') === 'zinc')>Zinc</option>
                        <option value="stone" @selected(old('base_color') === 'stone')>Stone</option>
                    </select>
                </div>
            </div>
            <div class="two-col">
                <div class="field"><label>Radius</label>
                    <select name="radius">
                        <option value="default" @selected(old('radius', 'default') === 'default')>Default</option>
                        <option value="large" @selected(old('radius') === 'large')>Large</option>
                        <option value="none" @selected(old('radius') === 'none')>None</option>
                    </select>
                </div>
                <div class="field"><label>Font</label>
                    <select name="font">
                        @foreach (['instrument-sans','inter','geist','poppins','outfit','plus-jakarta-sans'] as $f)
                            <option value="{{ $f }}" @selected(old('font', 'instrument-sans') === $f)>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="field"><label>Default appearance</label>
                <select name="default_appearance">
                    <option value="system" @selected(old('default_appearance', 'system') === 'system')>System</option>
                    <option value="light" @selected(old('default_appearance') === 'light')>Light</option>
                    <option value="dark" @selected(old('default_appearance') === 'dark')>Dark</option>
                </select>
            </div>
            <div class="two-col">
                <div class="field"><label>Sidebar layout</label>
                    <select name="sidebar_layout">
                        <option value="main" @selected(old('sidebar_layout', 'main') === 'main')>Main</option>
                        <option value="sideblock" @selected(old('sidebar_layout') === 'sideblock')>Sideblock</option>
                    </select>
                </div>
                <div class="field"><label>Card skin</label>
                    <select name="card_skin">
                        <option value="shadow" @selected(old('card_skin', 'shadow') === 'shadow')>Shadow</option>
                        <option value="bordered" @selected(old('card_skin') === 'bordered')>Bordered</option>
                        <option value="flat" @selected(old('card_skin') === 'flat')>Flat</option>
                        <option value="elevated" @selected(old('card_skin') === 'elevated')>Elevated</option>
                    </select>
                </div>
            </div>
            <div class="two-col">
                <div class="field"><label>Border radius</label>
                    <select name="border_radius">
                        <option value="none" @selected(old('border_radius', 'default') === 'none')>None</option>
                        <option value="sm" @selected(old('border_radius') === 'sm')>Small</option>
                        <option value="default" @selected(old('border_radius', 'default') === 'default')>Default</option>
                        <option value="md" @selected(old('border_radius') === 'md')>Medium</option>
                        <option value="lg" @selected(old('border_radius') === 'lg')>Large</option>
                        <option value="full" @selected(old('border_radius') === 'full')>Full</option>
                    </select>
                </div>
                <div class="field"><label>Menu color</label>
                    <select name="menu_color">
                        <option value="default" @selected(old('menu_color', 'default') === 'default')>Default</option>
                        <option value="primary" @selected(old('menu_color') === 'primary')>Primary</option>
                        <option value="muted" @selected(old('menu_color') === 'muted')>Muted</option>
                    </select>
                </div>
            </div>
            <div class="field"><label>Menu accent</label>
                <select name="menu_accent">
                    <option value="subtle" @selected(old('menu_accent', 'subtle') === 'subtle')>Subtle</option>
                    <option value="strong" @selected(old('menu_accent') === 'strong')>Strong</option>
                    <option value="bordered" @selected(old('menu_accent') === 'bordered')>Bordered</option>
                </select>
            </div>
            <div class="toggle-row">
                <div><div class="toggle-label">Allow user theme customization</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="allow_user_theme_customization" value="1" checked></div>
            </div>
            <div class="toggle-row">
                <div><div class="toggle-label">Allow user logo upload</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="allow_user_logo_upload" value="1"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="theme">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — keep defaults →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- AI Memory --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'memory')

        <h1>AI memory <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Vector memory / recall limits for AI features. Same as Settings → Memory.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="memory">
            <div class="field"><label>Embedding dimensions</label><input type="number" name="dimensions" value="{{ old('dimensions', 1536) }}" min="256" max="8192"></div>
            <div class="field"><label>Similarity threshold</label><input type="text" name="similarity_threshold" value="{{ old('similarity_threshold', '0.5') }}" placeholder="0.0–1.0"></div>
            <div class="two-col">
                <div class="field"><label>Recall limit</label><input type="number" name="recall_limit" value="{{ old('recall_limit', 10) }}" min="1" max="100"></div>
                <div class="field"><label>Middleware recall limit</label><input type="number" name="middleware_recall_limit" value="{{ old('middleware_recall_limit', 5) }}" min="1" max="50"></div>
            </div>
            <div class="two-col">
                <div class="field"><label>Oversample factor</label><input type="number" name="recall_oversample_factor" value="{{ old('recall_oversample_factor', 2) }}" min="1" max="10"></div>
                <div class="field"><label>Table name</label><input type="text" name="table" value="{{ old('table', 'memories') }}"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="memory">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — keep defaults →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Backups --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'backup')

        <h1>Backups <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Spatie backup retention. Same as Settings → Backup.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="backup">
            <div class="field"><label>Backup name</label><input type="text" name="name" value="{{ old('name', 'laravel-backup') }}"></div>
            <div class="two-col">
                <div class="field"><label>Keep all backups (days)</label><input type="number" name="keep_all_backups_for_days" value="{{ old('keep_all_backups_for_days', 7) }}" min="0"></div>
                <div class="field"><label>Keep daily backups (days)</label><input type="number" name="keep_daily_backups_for_days" value="{{ old('keep_daily_backups_for_days', 16) }}" min="0"></div>
            </div>
            <div class="two-col">
                <div class="field"><label>Keep weekly backups (weeks)</label><input type="number" name="keep_weekly_backups_for_weeks" value="{{ old('keep_weekly_backups_for_weeks', 8) }}" min="0"></div>
                <div class="field"><label>Keep monthly backups (months)</label><input type="number" name="keep_monthly_backups_for_months" value="{{ old('keep_monthly_backups_for_months', 4) }}" min="0"></div>
            </div>
            <div class="two-col">
                <div class="field"><label>Keep yearly backups (years)</label><input type="number" name="keep_yearly_backups_for_years" value="{{ old('keep_yearly_backups_for_years', 2) }}" min="0"></div>
                <div class="field"><label>Delete oldest when over (MB)</label><input type="number" name="delete_oldest_when_size_mb" value="{{ old('delete_oldest_when_size_mb', 5000) }}" min="0"></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="backup">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — keep defaults →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Feature flags (super-admin) --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'features')

        <h1>Feature flags <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Globally enable or disable features. Uncheck to disable a feature for the whole app. Change later in Settings → Feature Flag Settings.</p>
        @php
            $preset = session('install_preset', 'none');
            $disabledByPreset = $preset === 'internal' ? ['registration', 'api_access', 'contact'] : [];
        @endphp
        @if ($preset === 'internal')
            <div class="info-box">Internal tool preset: registration, API access, and contact form are unchecked by default.</div>
        @endif
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="features">
            <div class="select-all-row">
                <button type="button" class="btn-link" onclick="selectAllFeatures(true)">Enable all</button>
                <span style="color:#2a2a2a">·</span>
                <button type="button" class="btn-link" onclick="selectAllFeatures(false)">Disable all</button>
            </div>
            <div class="modules-grid" style="grid-template-columns: 1fr;">
                @foreach ($featureFlags ?? [] as $ff)
                    <label class="module-option">
                        <input type="checkbox" name="feature_enabled[{{ $ff['key'] }}]" value="1" @checked(!in_array($ff['key'], $disabledByPreset))>
                        <div>
                            <div class="module-label">{{ $ff['label'] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="features">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — enable all →</button>
        </form>
        <script>
            function selectAllFeatures(val) {
                document.querySelectorAll('form input[name^="feature_enabled"]').forEach(cb => cb.checked = val);
            }
        </script>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 17: Demo data --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'demo')

        <h1>Demo data</h1>
        <p class="subtitle">Optionally populate the app with realistic sample data. Each module is independent.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="demo">
            <div class="select-all-row">
                <button type="button" class="btn-link" onclick="selectAll(true)">Select all</button>
                <span style="color:#2a2a2a">·</span>
                <button type="button" class="btn-link" onclick="selectAll(false)">Clear all</button>
            </div>
            <div class="modules-grid">
                @foreach ($modules as $key => $module)
                    <label class="module-option">
                        <input type="checkbox" name="modules[]" value="{{ $key }}"
                            {{ in_array($key, ['users', 'organizations', 'content']) ? 'checked' : '' }}>
                        <div>
                            <div class="module-label">
                                {{ $module['label'] }}
                                @if (in_array($key, ['users', 'organizations', 'content']))
                                    <span class="module-badge">recommended</span>
                                @endif
                            </div>
                            <div class="module-desc">{{ $module['description'] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary">Install &amp; finish →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="demo">
            <button type="submit" class="btn btn-skip">Skip demo data, go to admin →</button>
        </form>
        <script>
            function selectAll(val) {
                document.querySelectorAll('input[name="modules[]"]').forEach(cb => cb.checked = val);
            }
        </script>

    @endif

</div>
<!-- Express install progress overlay -->
<div id="express-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:9999;display:none;align-items:center;justify-content:center;">
    <div style="background:#1a1a1a;border:1px solid #2a2a2a;border-radius:14px;padding:2.5rem;width:100%;max-width:420px;margin:1rem;">
        <div style="font-size:1rem;font-weight:600;color:#fff;margin-bottom:0.375rem;">Express Install</div>
        <div style="font-size:0.8125rem;color:#737373;margin-bottom:1.75rem;">Setting everything up — this takes about 30–60 seconds.</div>

        <ul id="express-steps" style="list-style:none;display:flex;flex-direction:column;gap:0.625rem;margin-bottom:1.75rem;">
            @php
            $expressSteps = [
                ['key' => 'migrate',      'label' => 'Running migrations'],
                ['key' => 'roles',        'label' => 'Seeding roles & permissions'],
                ['key' => 'gamification', 'label' => 'Seeding gamification data'],
                ['key' => 'mail_tpl',     'label' => 'Seeding mail templates'],
                ['key' => 'admin',        'label' => 'Creating admin user'],
                ['key' => 'settings',     'label' => 'Saving application settings'],
            ];
            @endphp
            @foreach($expressSteps as $es)
            <li id="express-step-{{ $es['key'] }}" style="display:flex;align-items:center;gap:0.625rem;font-size:0.875rem;color:#737373;">
                <span class="step-icon" style="width:18px;height:18px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" style="opacity:0.3"><circle cx="10" cy="10" r="7"/></svg>
                </span>
                <span>{{ $es['label'] }}</span>
            </li>
            @endforeach
        </ul>

        <div style="height:4px;background:#262626;border-radius:999px;overflow:hidden;margin-bottom:1rem;">
            <div id="express-bar" style="height:100%;width:0%;background:#3b82f6;border-radius:999px;transition:width 0.4s ease;"></div>
        </div>

        <div id="express-status-msg" style="font-size:0.8125rem;color:#737373;text-align:center;min-height:1.25em;"></div>
    </div>
</div>

<script>
(function() {
    var testUrl = '{{ route("install.test-connection") }}';
    var token = document.querySelector('input[name="_token"]');
    document.querySelectorAll('[data-test-connection]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var step = this.getAttribute('data-step');
            var formId = this.getAttribute('data-form-id');
            var resultId = this.getAttribute('data-result-id');
            var form = document.getElementById(formId);
            var resultEl = document.getElementById(resultId);
            if (!form || !resultEl) return;
            var fd = new FormData(form);
            fd.set('step', step);
            if (step === 'ai') {
                var aiTest = this.getAttribute('data-ai-test');
                if (aiTest) fd.set('ai_test', aiTest);
            }
            if (token) fd.set('_token', token.value);
            resultEl.textContent = '';
            resultEl.className = 'test-result';
            resultEl.classList.remove('success', 'error', 'visible');
            btn.disabled = true;
            fetch(testUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, body: j }; }); })
                .then(function(_ref) {
                    var ok = _ref.ok, body = _ref.body;
                    resultEl.classList.add('visible', ok ? 'success' : 'error');
                    resultEl.textContent = ok ? 'Connection successful.' : (body.message || 'Connection failed.');
                })
                .catch(function(e) {
                    resultEl.classList.add('visible', 'error');
                    resultEl.textContent = e.message || 'Connection check failed.';
                })
                .finally(function() { btn.disabled = false; });
        });
    });
})();

var EXPRESS_URL        = '{{ route("install.express") }}';
var EXPRESS_STATUS_URL = '{{ route("install.express.status") }}';
var EXPRESS_TOKEN      = '{{ csrf_token() }}';

var EXPRESS_STEPS = [
    {key:'migrate',      label:'Running migrations'},
    {key:'roles',        label:'Seeding roles & permissions'},
    {key:'gamification', label:'Seeding gamification data'},
    {key:'mail_tpl',     label:'Seeding mail templates'},
    {key:'admin',        label:'Creating admin user'},
    {key:'settings',     label:'Saving application settings'},
    {key:'demo',         label:'Seeding demo data'},
];

function startExpressInstall(options) {
    options = options || {};
    var overlay = document.getElementById('express-overlay');
    if (!overlay) return;
    document.querySelectorAll('.btn-express, .btn-secondary').forEach(function(b) { b.disabled = true; });
    overlay.style.display = 'flex';

    fetch(EXPRESS_URL, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': EXPRESS_TOKEN,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(options),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            showExpressError(data.error);
            return;
        }
        pollExpressStatus(data.progressFile, 0);
    })
    .catch(function(e) {
        showExpressError(e.message || 'Failed to start install.');
    });
}

function pollExpressStatus(cacheKey, attempt) {
    if (attempt > 120) {
        showExpressError('Install timed out. Please try again.');
        return;
    }
    fetch(EXPRESS_STATUS_URL + '?key=' + encodeURIComponent(cacheKey), {
        headers: { 'Accept': 'application/json' },
    })
    .then(function(r) {
        // Non-2xx (e.g., 401 Unauthenticated from /admin, or HTML redirect) means
        // EnsureNotInstalled middleware intercepted the request after install completed.
        if (!r.ok) {
            window.location.href = '/admin';
            return null;
        }
        var ct = r.headers.get('Content-Type') || '';
        if (ct.indexOf('application/json') === -1) {
            window.location.href = '/admin';
            return null;
        }
        return r.json();
    })
    .then(function(state) {
        if (!state) { return; }
        updateExpressUI(state);
        if (state.status === 'done') {
            document.getElementById('express-status-msg').textContent = 'Done! Redirecting…';
            setTimeout(function() {
                window.location.href = state.redirect || '/admin';
            }, 800);
        } else if (state.status === 'error') {
            showExpressError(state.message || 'An error occurred during install.');
        } else {
            setTimeout(function() { pollExpressStatus(cacheKey, attempt + 1); }, 1000);
        }
    })
    .catch(function() {
        setTimeout(function() { pollExpressStatus(cacheKey, attempt + 1); }, 2000);
    });
}

function updateExpressUI(state) {
    var steps = state.steps || {};
    var done  = 0;
    EXPRESS_STEPS.forEach(function(s) {
        var el     = document.getElementById('express-step-' + s.key);
        var status = steps[s.key] || 'pending';
        if (!el) return;

        var iconEl = el.querySelector('.step-icon');

        if (status === 'done') {
            el.style.color = '#22c55e';
            iconEl.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>';
            done++;
        } else if (status === 'running') {
            el.style.color = '#3b82f6';
            iconEl.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" style="animation:spin 1s linear infinite"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd"/></svg>';
        } else {
            el.style.color = '#737373';
            iconEl.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" style="opacity:0.3"><circle cx="10" cy="10" r="7"/></svg>';
        }
    });

    var pct = Math.round((done / EXPRESS_STEPS.length) * 100);
    var bar = document.getElementById('express-bar');
    if (bar) bar.style.width = pct + '%';

    var msgEl = document.getElementById('express-status-msg');
    var running = Object.keys(steps).find(function(k) { return steps[k] === 'running'; });
    if (msgEl && running) {
        var match = EXPRESS_STEPS.find(function(s) { return s.key === running; });
        msgEl.textContent = match ? match.label + '…' : '';
    }
}

function showExpressError(msg) {
    var btn     = document.getElementById('express-btn');
    var overlay = document.getElementById('express-overlay');
    var msgEl   = document.getElementById('express-status-msg');
    if (overlay) overlay.style.display = 'none';
    if (btn) { btn.disabled = false; }
    if (msgEl) { msgEl.textContent = ''; }
    alert('Express install failed: ' + msg);
}
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</body>
</html>
