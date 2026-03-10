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

        /* ── Progress bar (15 steps — use bar instead of dots) ── */
        .progress-wrap { margin-bottom: 1.75rem; }
        .progress-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem; }
        .progress-step-name { font-size: 0.75rem; font-weight: 600; color: #a3a3a3; text-transform: uppercase; letter-spacing: 0.06em; }
        .progress-count { font-size: 0.75rem; color: #525252; }
        .progress-bar-track { height: 3px; background: #2a2a2a; border-radius: 99px; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: #fff; border-radius: 99px; transition: width 0.3s ease; }

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
            transition: border-color 0.15s;
            font-family: inherit;
        }
        .field input:focus, .field select:focus, .field textarea:focus { border-color: #525252; }
        .field select { cursor: pointer; }
        .hint { font-size: 0.75rem; color: #525252; margin-top: 0.3rem; }

        /* ── Radio / toggle groups ─────────────────────── */
        .radio-group { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
        .radio-option { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border: 1px solid #2a2a2a; border-radius: 8px; cursor: pointer; transition: border-color 0.15s; }
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
        .toggle-wrap input[type=checkbox] { appearance: none; width: 36px; height: 20px; background: #2a2a2a; border-radius: 99px; cursor: pointer; transition: background 0.2s; }
        .toggle-wrap input[type=checkbox]:checked { background: #fff; }
        .toggle-wrap input[type=checkbox]::after { content: ''; position: absolute; top: 3px; left: 3px; width: 14px; height: 14px; background: #737373; border-radius: 50%; transition: left 0.2s, background 0.2s; }
        .toggle-wrap input[type=checkbox]:checked::after { left: 19px; background: #0f0f0f; }

        /* ── Extra fields ───────────────────────────────── */
        .extra-fields { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #2a2a2a; }

        /* ── Two-column ─────────────────────────────────── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

        /* ── Section divider ─────────────────────────────── */
        .section-label { font-size: 0.6875rem; font-weight: 600; color: #525252; text-transform: uppercase; letter-spacing: 0.08em; margin: 1.25rem 0 0.75rem; border-bottom: 1px solid #2a2a2a; padding-bottom: 0.375rem; }

        /* ── Buttons ────────────────────────────────────── */
        .btn { width: 100%; padding: 0.6875rem 1rem; border-radius: 8px; border: none; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: opacity 0.15s; margin-top: 1.25rem; }
        .btn:hover { opacity: 0.88; }
        .btn-primary { background: #fff; color: #0f0f0f; }
        .btn-skip { background: transparent; border: 1px solid #2a2a2a; color: #525252; }
        .btn-link { background: none; border: none; padding: 0; font-size: 0.8125rem; color: #737373; cursor: pointer; }
        .btn-link:hover { color: #a3a3a3; }

        /* ── Alerts ─────────────────────────────────────── */
        .alert { background: #1c0a0a; border: 1px solid #7f1d1d; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8125rem; color: #fca5a5; margin-bottom: 1.25rem; }
        .info-box { background: #0a0f1c; border: 1px solid #1e3a5f; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8125rem; color: #93c5fd; margin-bottom: 1rem; line-height: 1.6; }

        /* ── Modules grid ───────────────────────────────── */
        .select-all-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
        .modules-grid { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 0.25rem; }
        .module-option { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border: 1px solid #2a2a2a; border-radius: 8px; cursor: pointer; }
        .module-option:has(input:checked) { border-color: #525252; }
        .module-option input { margin-top: 0.2rem; flex-shrink: 0; }
        .module-label { font-size: 0.875rem; font-weight: 500; color: #e5e5e5; }
        .module-desc { font-size: 0.75rem; color: #737373; margin-top: 0.125rem; }
        .module-badge { font-size: 0.6875rem; background: #1f2937; color: #93c5fd; border-radius: 4px; padding: 1px 6px; margin-left: 0.375rem; }
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
        $allSteps = ['database', 'migrate', 'admin', 'app', 'tenancy', 'infrastructure', 'mail', 'search', 'ai', 'social', 'storage', 'broadcasting', 'seo', 'monitoring', 'demo'];
        $stepLabels = [
            'database' => 'Database', 'migrate' => 'Tables', 'admin' => 'Admin', 'app' => 'App',
            'tenancy' => 'Tenancy', 'infrastructure' => 'Infrastructure', 'mail' => 'Mail',
            'search' => 'Search', 'ai' => 'AI', 'social' => 'Social Auth',
            'storage' => 'Storage', 'broadcasting' => 'Broadcasting', 'seo' => 'SEO',
            'monitoring' => 'Monitoring', 'demo' => 'Demo Data',
        ];
        $currentIdx = array_search($step, $allSteps);
        $total = count($allSteps);
        $pct = round(($currentIdx / ($total - 1)) * 100);
    @endphp

    <div class="progress-wrap">
        <div class="progress-header">
            <span class="progress-step-name">{{ $stepLabels[$step] ?? $step }}</span>
            <span class="progress-count">{{ $currentIdx + 1 }} / {{ $total }}</span>
        </div>
        <div class="progress-bar-track">
            <div class="progress-bar-fill" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="alert">
            @foreach ($errors->all() as $err){{ $err }}<br>@endforeach
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 1: Database --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @if ($step === 'database')

        <h1>Database</h1>
        <p class="subtitle">Choose where your application stores data. SQLite requires no server and is perfect for getting started.</p>

        <form method="POST" action="{{ route('install.store') }}" id="db-form">
            @csrf
            <input type="hidden" name="step" value="database">
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="driver" value="sqlite" checked onchange="toggleDb(this)">
                    <div>
                        <div class="radio-label">SQLite</div>
                        <div class="radio-desc">File-based, zero configuration. Perfect for local and small deployments.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="driver" value="pgsql" onchange="toggleDb(this)">
                    <div>
                        <div class="radio-label">PostgreSQL</div>
                        <div class="radio-desc">Recommended for production. Full-featured relational database.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="driver" value="mysql" onchange="toggleDb(this)">
                    <div>
                        <div class="radio-label">MySQL / MariaDB</div>
                        <div class="radio-desc">Widely supported relational database.</div>
                    </div>
                </label>
            </div>
            <div class="extra-fields" id="server-fields" style="display:none">
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
            <button type="submit" class="btn btn-primary">Test connection &amp; continue →</button>
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
        <div class="info-box">Database connected. This takes a few seconds.</div>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="migrate">
            <button type="submit" class="btn btn-primary">Run setup →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 3: Admin --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'admin')

        <h1>Create admin account</h1>
        <p class="subtitle">The first super-admin account has full access to all settings, users, and Filament admin panels.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="admin">
            <div class="field"><label>Full name</label><input type="text" name="name" value="{{ old('name') }}" required autofocus></div>
            <div class="field"><label>Email address</label><input type="email" name="email" value="{{ old('email') }}" required></div>
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
            <div class="field">
                <label>Timezone</label>
                <select name="timezone">
                    @foreach (timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ $tz === 'UTC' ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </form>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- Step 5: Tenancy --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @elseif ($step === 'tenancy')

        <h1>Tenancy mode <span class="badge-opt">optional</span></h1>
        <p class="subtitle">Choose how your app handles organizations. Can be changed later in Settings → Tenancy.</p>
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="tenancy">
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="enabled" value="1" checked>
                    <div>
                        <div class="radio-label">Multi-organization</div>
                        <div class="radio-desc">Users can create and belong to multiple organizations. Ideal for SaaS, teams, and B2B apps.</div>
                    </div>
                </label>
                <label class="radio-option">
                    <input type="radio" name="enabled" value="0">
                    <div>
                        <div class="radio-label">Single-organization</div>
                        <div class="radio-desc">One organization for all users. Hides org management UI. Ideal for internal tools.</div>
                    </div>
                </label>
            </div>
            <div class="toggle-row">
                <div><div class="toggle-label">Users can create organizations</div><div class="radio-desc">Allow non-admin users to create their own orgs.</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="allow_user_org_creation" value="1" checked></div>
            </div>
            <div class="toggle-row" style="padding-top:0.625rem">
                <div><div class="toggle-label">Auto-create personal workspace</div><div class="radio-desc">Each new user gets a personal org on registration.</div></div>
                <div class="toggle-wrap"><input type="checkbox" name="auto_create_personal_org" value="1" checked></div>
            </div>
            <button type="submit" class="btn btn-primary">Save &amp; continue →</button>
        </form>
        <form method="POST" action="{{ route('install.store') }}" style="margin-top:0.5rem">
            @csrf
            <input type="hidden" name="step" value="tenancy">
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="btn btn-skip">Skip — keep defaults →</button>
        </form>

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
            <div class="field">
                <label>Mail driver</label>
                <select name="mailer" onchange="toggleMail(this.value)">
                    <option value="log">Log (development only)</option>
                    <option value="smtp">SMTP</option>
                    <option value="ses">Amazon SES</option>
                    <option value="postmark">Postmark</option>
                    <option value="resend">Resend</option>
                    <option value="mailgun">Mailgun</option>
                </select>
            </div>
            <div id="smtp-fields" style="display:none">
                <div class="two-col">
                    <div class="field"><label>SMTP host</label><input type="text" name="smtp_host" value="{{ old('smtp_host', 'smtp.mailtrap.io') }}"></div>
                    <div class="field"><label>SMTP port</label><input type="number" name="smtp_port" value="{{ old('smtp_port', '587') }}"></div>
                </div>
                <div class="two-col">
                    <div class="field"><label>Username</label><input type="text" name="smtp_username" value="{{ old('smtp_username') }}"></div>
                    <div class="field"><label>Password</label><input type="password" name="smtp_password"></div>
                </div>
                <div class="field">
                    <label>Encryption</label>
                    <select name="smtp_encryption">
                        <option value="tls">TLS (port 587)</option>
                        <option value="ssl">SSL (port 465)</option>
                        <option value="">None</option>
                    </select>
                </div>
            </div>
            <div class="section-label">From address</div>
            <div class="two-col">
                <div class="field"><label>From email</label><input type="email" name="from_address" value="{{ old('from_address') }}" placeholder="hello@example.com"></div>
                <div class="field"><label>From name</label><input type="text" name="from_name" value="{{ old('from_name', config('app.name')) }}"></div>
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
                <div class="field">
                    <label>Protocol</label>
                    <select name="typesense_protocol">
                        <option value="http">HTTP</option>
                        <option value="https">HTTPS</option>
                    </select>
                </div>
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
            <div class="field">
                <label>Default provider</label>
                <select name="provider" onchange="toggleAi(this.value)">
                    <option value="">None / skip</option>
                    <option value="openrouter" selected>OpenRouter (free models available)</option>
                    <option value="openai">OpenAI</option>
                    <option value="anthropic">Anthropic (Claude)</option>
                    <option value="groq">Groq</option>
                    <option value="gemini">Google Gemini</option>
                    <option value="xai">xAI (Grok)</option>
                    <option value="deepseek">DeepSeek</option>
                    <option value="mistral">Mistral</option>
                    <option value="ollama">Ollama (local, no key needed)</option>
                </select>
            </div>
            <div id="ai-key-field">
                <div class="field"><label>API key</label><input type="password" name="api_key" placeholder="sk-..."><p class="hint">Leave blank for Ollama.</p></div>
                <div class="field"><label>Default model <span style="color:#525252">(optional)</span></label><input type="text" name="model" value="{{ old('model') }}" placeholder="e.g. gpt-4o, claude-3-5-sonnet, deepseek/deepseek-r1-0528:free"></div>
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
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <input type="hidden" name="step" value="broadcasting">
            <div class="info-box">Generate credentials: <code style="background:#0f0f0f;border-radius:4px;padding:1px 6px;font-size:0.8em">php artisan reverb:install</code>, then paste them here.</div>
            <div class="two-col">
                <div class="field"><label>App ID</label><input type="text" name="reverb_app_id" value="{{ old('reverb_app_id') }}"></div>
                <div class="field"><label>App Key</label><input type="text" name="reverb_app_key" value="{{ old('reverb_app_key') }}"></div>
            </div>
            <div class="field"><label>App Secret</label><input type="password" name="reverb_app_secret"></div>
            <div class="two-col">
                <div class="field"><label>Host</label><input type="text" name="reverb_host" value="{{ old('reverb_host', 'localhost') }}"></div>
                <div class="field"><label>Port</label><input type="number" name="reverb_port" value="{{ old('reverb_port', '8080') }}"></div>
            </div>
            <div class="field">
                <label>Scheme</label>
                <select name="reverb_scheme">
                    <option value="http">http (local)</option>
                    <option value="https">https (production)</option>
                </select>
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
                <textarea name="meta_description" rows="3" maxlength="160" style="width:100%;background:#0f0f0f;border:1px solid #2a2a2a;border-radius:8px;padding:0.625rem 0.75rem;font-size:0.875rem;color:#e5e5e5;resize:vertical;font-family:inherit">{{ old('meta_description') }}</textarea>
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
            <div class="field">
                <label>Error sample rate</label>
                <select name="sentry_sample_rate">
                    <option value="1.0" selected>1.0 — capture all errors</option>
                    <option value="0.5">0.5 — capture 50%</option>
                    <option value="0.1">0.1 — capture 10% (high-traffic)</option>
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
    {{-- Step 15: Demo data --}}
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
</body>
</html>
