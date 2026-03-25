# Starter Kit Enhancements — Design Spec

> **Date**: 2026-03-25
> **Timeline**: 1–2 weeks (MVP scope, ruthlessly trimmed)
> **Approach**: Parallel tracks — Quick Wins (3–4 days) + AI Stack (7–10 days)
> **Origin**: FusionCRM v4 infrastructure requirements

---

## Overview

Five generic SaaS infrastructure features that any module (CRM, HR, billing, future products) can extend. Built in the starter kit so FusionCRM v4 PRDs shrink and all future projects benefit.

### Implementation Tracks

**Track 1 — Quick Wins (3–4 days):**
1. Shared DataTable Views
2. PDF Generation Pipeline
3. Scheduled Email Triggers

**Track 2 — AI Stack (7–10 days):**
4. RAG Pipeline (pgvector)
5. AI Agent Service + Global Chat

---

## 1. Shared DataTable Views

### Problem
`data_table_saved_views` is per-user only. No team sharing or system-wide default views.

### Data Model

Migration adds 4 columns to `data_table_saved_views`:

| Column | Type | Notes |
|--------|------|-------|
| `organization_id` | `foreignId()->nullable()` | NULL for private views (backward compat) |
| `is_shared` | `boolean, default false` | Visible to org members |
| `is_system` | `boolean, default false` | Admin-created defaults |
| `created_by` | `foreignId()->nullable()` | Track who created it |

Index on `(organization_id)` where `is_shared = true`.

### Scoping Logic

```
User sees:
  My Views     → user_id = auth.id AND is_shared = false
  Team Views   → organization_id = tenant.id AND is_shared = true AND is_system = false
  System Views → organization_id = tenant.id AND is_system = true
```

### Backend Changes

- **Migration**: Add columns + partial index.
- **SavedView model**: Add scopes (`forUser()`, `sharedInOrg()`, `systemInOrg()`) and a `grouped()` method returning all three categories.
- **SavedView controller**: Update store/update to accept `is_shared` toggle. Only users with `manage system views` permission can set `is_system = true`.

### Frontend Changes

- **Saved views dropdown**: Group into 3 sections with headers (My Views / Team Views / System Views).
- **Save dialog**: Add "Share with team" toggle. Show "Set as system view" checkbox for admins.
- **View badge**: Small indicator showing shared/system status.

### Out of Scope (MVP)

- No "duplicate shared view to my views."
- No per-view permissions (view vs. edit) — shared means visible, only creator/admin can edit.
- No cross-org sharing.

### Acceptance Criteria

- [ ] Migration adds `organization_id`, `is_shared`, `is_system`, `created_by`
- [ ] Saved view dropdown groups by scope (My / Team / System)
- [ ] Creating a view has "Share with team" toggle
- [ ] System views created by admin visible to all org members
- [ ] Existing private views continue to work (backward compatible)

---

## 2. PDF Generation Pipeline

### Problem
`spatie/laravel-pdf` is installed but no reusable async PDF generation pattern exists.

### Action

```
app/Actions/GeneratePdf.php
```

Single `handle()` method:

| Parameter | Type | Notes |
|-----------|------|-------|
| `$view` | `string` | Blade view name (e.g., `billing::invoice`, `crm::flyer`) |
| `$data` | `array` | Data passed to the view |
| `$filename` | `string` | Output filename |
| `$attachTo` | `?Model` | Optional: attach to model via Spatie Media Library |
| `$collection` | `?string` | Media Library collection name (default: `'documents'`) |

Returns the file path. Internally: render view → generate PDF via `Spatie\LaravelPdf\Facades\Pdf` → if `$attachTo`, add to Media Library → return path.

### Job

```
app/Jobs/GeneratePdfJob.php
```

- Implements `ShouldQueue`.
- Wraps `GeneratePdf` action.
- Dispatches `DatabaseNotification` to the user on completion (with download link).
- On failure: notifies user with error message, logs to `pdf` channel.

### Usage

```php
// Sync (small PDFs, immediate download)
$path = app(GeneratePdf::class)->handle('billing::invoice', $data, 'invoice-123.pdf', $invoice);

// Async (large PDFs, background generation)
GeneratePdfJob::dispatch('crm::flyer', $lotData, "lot-{$lot->id}-flyer.pdf", $lot, auth()->id());
```

### Out of Scope (MVP)

- No PDF template builder UI — modules provide their own Blade views.
- No batch PDF generation.
- No PDF preview before generation.
- No configurable paper size/orientation per call — sensible defaults (A4, portrait), modules override in Blade.

### Acceptance Criteria

- [ ] `GeneratePdf` action renders any Blade view to PDF
- [ ] `GeneratePdfJob` wraps action for async queue processing
- [ ] Optional auto-attach to model via Spatie Media Library
- [ ] User notification on completion
- [ ] Works with any module's views

---

## 3. Scheduled Email Triggers

### Problem
The existing `TriggersDatabaseMail` pattern handles event → template wiring, but lacks scheduling (delayed sends) and an admin UI for managing trigger mappings without code changes.

### What Already Exists

- Events implement `TriggersDatabaseMail` + `CanTriggerDatabaseMail`.
- Events define `getName()`, `getDescription()`, `getRecipients()`, `getAttachments()`.
- Events registered in `config/database-mail.php` under `'events'`.
- Templates stored in DB via `martinpetricko/laravel-database-mail`.
- All sent emails logged by `backstage/laravel-mails`.

### Data Model

New `mail_trigger_schedules` table:

| Column | Type | Notes |
|--------|------|-------|
| `id` | `bigIncrements` | |
| `organization_id` | `foreignId` | Org-scoped |
| `event_class` | `string` | The `TriggersDatabaseMail` event class |
| `template_id` | `foreignId` | Reference to `mail_templates` |
| `delay_minutes` | `integer, nullable` | NULL = immediate, otherwise delay via Snooze |
| `is_active` | `boolean, default true` | Toggle without deleting |
| `feature_flag` | `string, nullable` | Only fires if this Pennant feature is active |
| `created_by` | `foreignId, nullable` | |

### ScheduledMailDispatcher Service

```
app/Services/ScheduledMailDispatcher.php
```

- Listens after the existing `DatabaseMail` listener fires.
- Checks `mail_trigger_schedules` for a matching org + event.
- If `delay_minutes` is set: schedules via `thomasjohnkane/snooze` instead of sending immediately.
- If `is_active = false`: suppresses the send.
- If `feature_flag` is set: checks Pennant before sending.
- Falls back to existing immediate behavior if no schedule record exists.

### Filament Admin Page

```
app/Filament/Admin/Pages/ManageMailTriggers.php
```

- Lists all registered `TriggersDatabaseMail` events (from `config/database-mail.php`).
- For each event: shows name, description, current template, delay, active status.
- Admin can: assign/change template, set delay, toggle active, set feature flag requirement.
- Org-scoped — each org can have different trigger configurations.

### Out of Scope (MVP)

- No model observer-based triggers — stick with explicit event classes.
- No complex conditions (e.g., "only if contact.type = VIP") — feature flag gating is the only filter.
- No email preview from the admin page.
- No trigger history/audit log beyond what `backstage/laravel-mails` already captures.

### Acceptance Criteria

- [ ] `ScheduledMailDispatcher` service matches org + event to schedule config
- [ ] Immediate send or scheduled via Snooze based on `delay_minutes`
- [ ] `is_active` toggle suppresses sends without deleting the trigger
- [ ] Feature flag gating via Pennant
- [ ] Filament admin page to manage trigger mappings per org
- [ ] Falls back to existing immediate behavior when no schedule record exists
- [ ] All sent emails continue to be auto-logged by `backstage/laravel-mails`

---

## 4. RAG Pipeline (pgvector)

### Problem
pgvector is installed and `embedding_demos` table exists, but no generic "make any model RAG-searchable" infrastructure.

### Configuration

In `config/ai.php`, add an `embeddings` key:

```php
'embeddings' => [
    'provider' => env('EMBEDDING_PROVIDER', 'openai'),
    'model' => env('EMBEDDING_MODEL', 'text-embedding-3-small'),
    'dimensions' => env('EMBEDDING_DIMENSIONS', 1536),
],
```

Provider-agnostic from day one — different orgs may use different embedding providers (BYOK).

### Data Model

```
database/migrations/xxxx_create_model_embeddings_table.php
```

| Column | Type | Notes |
|--------|------|-------|
| `id` | `bigIncrements` | |
| `organization_id` | `foreignId` | Org-scoped |
| `embeddable_type` | `string(50)` | Morph type |
| `embeddable_id` | `unsignedBigInteger` | Morph ID |
| `chunk_index` | `integer, default 0` | Future-proofing for chunked embeddings |
| `embedding` | `vector(1536)` | Default dimension; hardcoded in migration, configurable for new installs via `config/ai.php` |
| `content_hash` | `string(64)` | SHA256 of source text — skip re-embedding if unchanged |
| `metadata` | `json, nullable` | Optional extra context |

Indexes:
- Unique on `(embeddable_type, embeddable_id, chunk_index)`
- `organization_id` for scoped queries
- IVFFlat on `embedding` using `vector_cosine_ops`

### HasEmbeddings Trait

```
app/Models/Concerns/HasEmbeddings.php
```

- Models implement `toEmbeddableText(): string` — returns the text to embed.
- Trait registers `created` and `updated` model events that dispatch `GenerateEmbeddingJob`.
- Provides `embedding()` morphOne relationship to `ModelEmbedding`.
- Provides `needsReembedding(): bool` — compares current `sha256(toEmbeddableText())` against stored `content_hash`.

### GenerateEmbeddingJob

```
app/Jobs/GenerateEmbeddingJob.php
```

- Implements `ShouldQueue`, `ShouldBeUnique` (keyed on `embeddable_type:embeddable_id`).
- Checks `needsReembedding()` — skips if content unchanged.
- Calls `laravel/ai` embeddings API with configured provider/model.
- Upserts into `model_embeddings`.
- Rate-limited via `spatie/laravel-rate-limited-job-middleware`.

### SemanticSearchService

```
app/Services/SemanticSearchService.php
```

Fluent API:

```php
SemanticSearchService::query('available lots under $500k')
    ->scope(Contact::class, Project::class)  // restrict to model types
    ->forOrganization($orgId)                // required, always org-scoped
    ->limit(10)                              // default 10
    ->threshold(0.7)                         // minimum cosine similarity
    ->get();                                 // Collection of models with similarity_score
```

Internally:
1. Embed the query string using configured provider/model.
2. Run pgvector cosine distance query filtered by `organization_id` and `embeddable_type`.
3. Hydrate Eloquent models via morph map.
4. Return collection sorted by similarity with `similarity_score` appended.

### Artisan Command

```
php artisan embeddings:refresh {model} [--chunk=500]
```

Queries all instances of a model, dispatches `GenerateEmbeddingJob` for each in chunks. Used for: initial data load, provider changes, `toEmbeddableText()` changes.

### Out of Scope (MVP)

- No chunking logic — `chunk_index` column exists but trait produces single chunk (index 0). Models needing chunking can override a `chunkText(): array` method later.
- No hybrid search (semantic + keyword) — pure vector similarity. Scout+Typesense handles keyword search separately.
- No embedding management UI — `embeddings:refresh` + Horizon failed jobs covers debugging.
- No multi-vector per model.

### Acceptance Criteria

- [ ] `HasEmbeddings` trait available for any model
- [ ] `model_embeddings` migration with pgvector index and `chunk_index` column
- [ ] Auto-embed on model save (async job, skip if content unchanged)
- [ ] `SemanticSearchService` with fluent org-scoped API
- [ ] Provider-agnostic — embedding provider/model/dimensions from `config/ai.php`
- [ ] `php artisan embeddings:refresh {model}` for batch re-indexing
- [ ] Rate-limited job middleware to respect provider API limits

---

## 5. AI Agent Service + Global Chat

### Problem
No orchestration layer letting modules plug their data into the AI agent. Chat UI needs rework for global access, structured responses, streaming, voice, and file uploads.

### 5a. ModuleToolRegistry

```
app/Support/ModuleToolRegistry.php
```

A singleton collecting AI tools from all enabled modules at boot time.

**Module registration:**

```php
// In any module's ModuleProvider:
public function registerAiTools(): array
{
    return [
        ContactSearchTool::class,
        ProjectLookupTool::class,
    ];
}
```

**Registry behavior:**
- At boot, iterates all enabled `ModuleProvider` classes and calls `registerAiTools()` if the method exists.
- Each tool class implements `requiredFeature(): ?string` — returns the Pennant feature flag that gates it (or null if always available).
- `getToolsForOrganization(Organization $org): array` filters tools by: module enabled + feature flag active for org's plan.
- Cached per org per request.

**Starter kit base tools** in `app/Ai/Tools/` are always available (not module-gated): e.g., `UserProfileTool`, `SearchTool` (wraps `SemanticSearchService`).

### 5b. OrgScopedAgent

```
app/Ai/OrgScopedAgent.php
```

A wrapper around `laravel/ai` Agent injecting tenant context into every interaction.

**Responsibilities:**

1. **Org context injection** — Every tool call automatically receives `organization_id` from `TenantContext`. Tools never handle scoping themselves.
2. **Tool resolution** — Pulls available tools from `ModuleToolRegistry::getToolsForOrganization()` at conversation start.
3. **Conversation scoping** — Conversations in `agent_conversations` scoped to `user_id` + `organization_id`. Switching orgs shows different history.
4. **Credit deduction** — Wraps each agent turn with credit check/deduction. BYOK orgs (with own API keys via org-overridable AI settings group in `organization_settings` table) bypass credits. Check: org has AI settings override with API key → bypass, otherwise → deduct.
5. **Page context** — Accepts optional context payload:

```php
$agent = OrgScopedAgent::make()
    ->withContext([
        'page' => '/crm/contacts/123',
        'entity_type' => 'contact',
        'entity_id' => 123,
    ]);
```

**Not in scope (MVP):**
- No custom system prompts per org — standard prompt includes org name and available tools.
- No conversation branching or forking.
- No multi-agent orchestration (single agent with multiple tools).

### 5c. Context Injection (Frontend → Backend)

**Frontend — `useAgentContext()` hook:**

```
resources/js/hooks/use-agent-context.ts
```

- Reads current Inertia page URL and props.
- Extracts entity context when available (page props contain a model object with `id`).
- Returns: `{ page, entity_type?, entity_id?, entity_name? }`.
- Global chat widget sends context with every message.

**Entity detection via `module.json`:**

```json
{
    "contextual_models": {
        "contact": "App\\Models\\Contact",
        "project": "Modules\\Crm\\Models\\Project"
    }
}
```

Hook checks if current page props contain any key matching a registered contextual model name.

**Backend flow:**
1. Chat message request includes `context` payload.
2. `OrgScopedAgent` receives it.
3. Tools implementing `ContextAwareTool` interface get `setContext(array $context)` called.
4. Agent injects context before each tool call.

**Not in scope:** No auto entity preloading, no context history, no cross-tab awareness.

### 5d. Chat UI — 2-Panel Layout

**3 States:**

| State | Trigger | Size |
|-------|---------|------|
| Collapsed | Default | Floating button (bottom-right) + `⌘K`/`Ctrl+K` hint + unread badge |
| Slide-over | Click button or `⌘K` | 560px: 160px conversation list + 400px active chat |
| Full page | Click expand or navigate to `/chat` | Same 2-panel layout, full width |

**Left Panel (Conversation List):**
- New Chat button
- Search conversations
- Conversation items: title, preview, timestamp, unread dot
- Active conversation highlighted with teal left border
- Scoped to current org

**Right Panel (Active Chat):**
- Context bar: shows current page/entity the agent is aware of
- Message area: user messages (right-aligned) + agent messages (left-aligned)
- Agent messages contain `blocks` array for structured rendering
- Voice playback button on agent messages (TTS via `laravel/ai`)

**Structured Response Renderers** (using Thesys C1 generative UI components, already integrated in the starter kit):

```
resources/js/components/chat/renderers/
├── table-renderer.tsx      # Tabular data (search results, lists)
├── card-renderer.tsx       # Single entity cards (contact, project)
├── chart-renderer.tsx      # Simple charts (pipeline, stats)
├── action-renderer.tsx     # Suggested actions (clickable buttons)
└── renderer-registry.tsx   # Maps response types → components
```

Modules register custom renderers:

```typescript
// modules/crm/resources/js/chat-renderers.ts
import { registerRenderer } from '@/components/chat/renderers/renderer-registry';
import { LotCardRenderer } from './renderers/lot-card-renderer';
registerRenderer('crm:lot-card', LotCardRenderer);
```

**Input Row:**
- File attachment button (stored via Media Library on conversation, passed to agent as context; vision-capable models handle images)
- Voice input button (Web Speech API, red active state with waveform visualization)
- Text input field
- Send button

**Streaming:**
- SSE-based streaming via `laravel/ai` streaming support
- Tokens render progressively with typing cursor
- Structured blocks render after stream completes

**Keyboard Shortcut:**
- `⌘K` (Mac) / `Ctrl+K` (Windows) toggles the slide-over from any page. App-scoped only — does not capture when an input/textarea is focused. If conflicts arise with other tools, can be remapped via user settings.

**Not in scope (MVP):**
- No @-mention entity references (context injection handles this automatically).
- No conversation sharing between users.
- No conversation pinning or folders.

### Acceptance Criteria

- [ ] `ModuleToolRegistry` collects tools from all enabled modules at boot
- [ ] Tools filtered by plan-gated features (Pennant + FeatureHelper)
- [ ] `OrgScopedAgent` wraps base agent with org context, credit deduction, BYOK bypass
- [ ] Context injection sends current page/entity from frontend to agent
- [ ] Tools implementing `ContextAwareTool` receive page context automatically
- [ ] 2-panel chat UI: conversation list + active chat
- [ ] 3 states: collapsed (floating button) → slide-over (560px) → full page (/chat)
- [ ] `⌘K` / `Ctrl+K` keyboard shortcut toggles slide-over
- [ ] SSE streaming responses with progressive token rendering
- [ ] Voice input via Web Speech API
- [ ] Voice output (TTS) via `laravel/ai` with play/pause
- [ ] File/image uploads stored in Media Library, passed to agent
- [ ] Structured response renderers: table, card, chart, action
- [ ] Module-registered custom renderers via `renderer-registry.tsx`
- [ ] Conversations scoped to user + org
- [ ] Auto-titled conversations
- [ ] Unread indicators on conversation list

---

## Design Decisions

| Decision | Rationale |
|----------|-----------|
| Provider-agnostic embeddings | BYOK support — orgs use different providers |
| `chunk_index` column but no chunking logic | Future-proofing without complexity |
| `mail_trigger_schedules` table over config | Admin-configurable per org without deploys |
| 2-panel chat layout | Conversations always visible, no tab switching |
| 560px slide-over | Wide enough for structured renderers, narrow enough to keep page visible |
| `module.json` for contextual models | Declarative, no code changes to add context awareness |
| Single agent with multiple tools | MVP simplicity, multi-agent orchestration is v2 |
| Renderer registry pattern | Modules extend chat UI without modifying starter kit code |

## Dependencies

```
Feature 1 (DataTable Views)     → independent
Feature 2 (PDF Pipeline)        → independent
Feature 3 (Email Triggers)      → independent
Feature 4 (RAG Pipeline)        → independent
Feature 5a (ModuleToolRegistry) → independent
Feature 5b (OrgScopedAgent)     → depends on 5a
Feature 5c (Context Injection)  → depends on 5b
Feature 5d (Chat UI)            → depends on 5b, 5c
Feature 5 (full AI stack)       → integrates with Feature 4 (SemanticSearchService as a base tool)
```
