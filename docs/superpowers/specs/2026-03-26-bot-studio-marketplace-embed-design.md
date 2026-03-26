# Bot Studio: Marketplace + Embed Widget — Design Spec (Specs B + C)

> **Date**: 2026-03-26
> **Timeline**: Flexible — build it properly
> **Depends on**: Bot Studio Core (Spec A, completed)
> **Module**: `modules/bot-studio/` (extending existing module)

---

## Overview

Two distribution features for Bot Studio agents:

**Spec B — Marketplace:** Browse and install agents published by other organizations. Ratings/reviews, featured section, search/filter.

**Spec C — Embed Widget:** Deploy agents on external websites via `<script>` tag. Standalone JS bundle, token-based auth, theme customization, domain whitelist.

---

## Spec B: Agent Marketplace

### Data Model

#### agent_installs

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigIncrements | |
| `organization_id` | foreignId, cascadeOnDelete | Org that installed |
| `agent_definition_id` | foreignId, cascadeOnDelete | Original published agent |
| `installed_definition_id` | foreignId, nullable | Local copy created |
| `installed_by` | foreignId, nullable | User who installed |
| `timestamps` | | |

Unique on `(organization_id, agent_definition_id)`.

#### agent_reviews

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigIncrements | |
| `agent_definition_id` | foreignId, cascadeOnDelete | |
| `organization_id` | foreignId, cascadeOnDelete | Reviewer's org |
| `user_id` | foreignId, cascadeOnDelete | |
| `rating` | tinyInteger | 1-5 stars |
| `review` | text, nullable | Optional text |
| `timestamps` | | |

Unique on `(agent_definition_id, user_id)` — one review per user.

#### agent_definitions additions

Add counter cache columns:
- `average_rating` — decimal(2,1), default 0
- `review_count` — unsignedInteger, default 0
- `install_count` — unsignedInteger, default 0
- `category` — string(50), nullable (for marketplace filtering)

### Models

**AgentInstall**: Relationships — `organization()`, `agentDefinition()`, `installedDefinition()`, `installer()`.

**AgentReview**: Relationships — `agentDefinition()`, `organization()`, `user()`. On create/delete, recalculate `average_rating` and `review_count` on the parent definition.

### Routes

All under `auth + tenant` middleware:

| Method | Route | Action | Notes |
|--------|-------|--------|-------|
| GET | `/marketplace` | `MarketplaceController@index` | Browse/search |
| GET | `/marketplace/{agentDefinition:slug}` | `MarketplaceController@show` | Agent detail |
| POST | `/marketplace/{agentDefinition:slug}/install` | `MarketplaceController@install` | Install to org |
| POST | `/marketplace/{agentDefinition:slug}/review` | `MarketplaceController@review` | Submit rating/review |
| DELETE | `/marketplace/{agentDefinition:slug}/review` | `MarketplaceController@deleteReview` | Remove own review |

### MarketplaceController

- `index()`: Query `agent_definitions` where `is_published = true`. Paginate. Filter by category, sort by rating/installs/newest. Search by name/description. Featured section: `is_featured = true`. Render `marketplace/index`.
- `show()`: Load definition with reviews (paginated), install count. Check if current org already installed. Render `marketplace/show`.
- `install()`: Duplicate definition into user's org (like template fork). Create `agent_installs` record. Increment `install_count`. Redirect to user's edit page for the copy. Requires `bot_studio_pro` feature.
- `review()`: Validate rating (1-5) + optional review text. Create/update `agent_reviews`. Recalculate counter caches. Cannot review own agent.
- `deleteReview()`: Delete own review. Recalculate counters.

### Pages

**marketplace/index.tsx:**
- Search bar + category filter dropdown + sort selector (Popular, Highest Rated, Newest)
- Featured agents section at top (horizontal scroll, `is_featured = true`)
- Grid of agent cards: avatar, name, creator org name, description, rating (stars), install count, category badge
- Pagination

**marketplace/show.tsx:**
- Agent detail header: avatar, name, creator org, description, rating summary (average + count)
- "Install" button (or "Installed" badge if already installed)
- Conversation starters preview
- Tool list (names only, no details for security)
- Reviews section: rating breakdown (5-star bars), individual reviews with user name + rating + text
- "Write a review" form (if installed and not own agent)

### Publish Flow

On the agent edit page (Settings tab), add:
- "Publish to Marketplace" toggle (requires `bot_studio_pro`)
- Category selector (General, Customer Support, Sales, Data Analysis, Education, Other)
- Publishing sets `is_published = true` + selected `category`
- Unpublishing sets `is_published = false` — existing installs keep working (they have copies)

### Plan-Gating

- **Browsing marketplace**: available to all `bot_studio` users (basic + pro)
- **Installing agents**: requires `bot_studio_pro` (or `bot_studio_basic` — agents installed from marketplace don't count against agent limit)
- **Publishing to marketplace**: requires `bot_studio_pro`

---

## Spec C: Embed Widget

### Data Model

#### agent_embed_tokens

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigIncrements | |
| `agent_definition_id` | foreignId, cascadeOnDelete | |
| `organization_id` | foreignId, cascadeOnDelete | Credits billed here |
| `token` | string(64), unique | Hashed token |
| `name` | string(100) | e.g., "Production site" |
| `allowed_domains` | json, default '[]' | CORS whitelist (empty = allow all) |
| `is_active` | boolean, default true | |
| `last_used_at` | timestamp, nullable | |
| `request_count` | unsignedBigInteger, default 0 | |
| `rate_limit_per_minute` | unsignedInteger, default 30 | |
| `timestamps` | | |

#### agent_definitions additions

- `embed_enabled` — boolean, default false
- `embed_theme` — json, default '{}'

### Embed Theme Schema

```json
{
    "primary_color": "#0d9488",
    "position": "bottom-right",
    "greeting": "Hi! How can I help you?",
    "placeholder": "Type a message...",
    "avatar_url": null,
    "show_powered_by": true
}
```

### Models

**AgentEmbedToken**: Relationships — `agentDefinition()`, `organization()`. Generate token via `Str::random(48)`, store as `hash('sha256', $plainToken)`. Return plain token only on creation.

### EmbedTokenService

```
modules/bot-studio/src/Services/EmbedTokenService.php
```

- `create(AgentDefinition $def, string $name, array $domains): {token: AgentEmbedToken, plainToken: string}` — generates token, hashes for storage, returns both
- `verify(string $plainToken): ?AgentEmbedToken` — hashes input, looks up, checks `is_active`, validates domain
- `recordUsage(AgentEmbedToken $token): void` — increments `request_count`, updates `last_used_at`

### Routes

**Public (no auth — token-based):**

| Method | Route | Action | Notes |
|--------|-------|--------|-------|
| GET | `/api/embed/{token}/config` | `EmbedApiController@config` | Agent name, theme, starters |
| POST | `/api/embed/{token}/chat` | `EmbedApiController@chat` | NDJSON streaming |

**Authenticated (bot-studio routes):**

| Method | Route | Action | Notes |
|--------|-------|--------|-------|
| POST | `/bot-studio/{slug}/embed-tokens` | `EmbedTokenController@store` | Create token |
| PUT | `/bot-studio/{slug}/embed-tokens/{embedToken}` | `EmbedTokenController@update` | Update name/domains |
| DELETE | `/bot-studio/{slug}/embed-tokens/{embedToken}` | `EmbedTokenController@destroy` | Revoke token |
| PUT | `/bot-studio/{slug}/embed` | `EmbedController@updateTheme` | Update embed theme |

**Standalone public page:**

| Method | Route | Action | Notes |
|--------|-------|--------|-------|
| GET | `/chat/{agentDefinition:slug}` | `EmbedController@standalone` | Full-page public chat |

### EmbedApiController

- `config()`: Look up token, return agent name, avatar, theme config, conversation starters. CORS headers based on `allowed_domains`.
- `chat()`: Verify token, check rate limit, validate domain. Use `AgentRunner` to stream response. Credits deducted from token's org. NDJSON format. **No tool access** — embedded agents use knowledge/RAG only (security boundary: external users should not trigger org tools).

### Widget JS Bundle

Standalone file at `/js/bot-studio-embed.js`:

- **Build**: Separate Vite entry point → single self-contained JS file (~30KB). Vanilla JS, no React dependency on host site.
- **Shadow DOM**: All UI rendered inside a shadow root to avoid style conflicts with host site.
- **Integration**: `<script src="https://app.com/js/bot-studio-embed.js" data-token="abc123"></script>`
- **Behavior**:
  1. Reads `data-token` from script tag
  2. Fetches `/api/embed/{token}/config` for agent info + theme
  3. Renders floating chat button (themed: primary_color, position)
  4. On click, opens chat panel with greeting message
  5. User messages POST to `/api/embed/{token}/chat`
  6. Streaming responses parsed from NDJSON
  7. Conversation stored in localStorage (client-side only, no server persistence for anonymous users)

### Standalone Chat Page

`/chat/{slug}` — a public, full-page Inertia page (or Blade view) that renders the chat UI for an agent. No embed token needed — uses the agent's public slug. Requires `embed_enabled = true` on the definition. Anonymous users get a session-based conversation. Credits billed to agent's org.

### Embed Tab on Edit Page

Add "Embed" tab to the split-screen editor:

- **Toggle**: "Enable embedding" (sets `embed_enabled`)
- **Token management**: Create/delete/rename tokens. Show hashed preview, plain token shown only on creation with copy button.
- **Domain whitelist**: Per token, comma-separated domains
- **Theme customization**: Color picker (primary_color), position selector (bottom-right/bottom-left), greeting text, placeholder text, show powered-by toggle
- **Embed code generator**: Copy-to-clipboard `<script>` tag
- **Standalone URL**: Copy-to-clipboard `https://app.com/chat/{slug}`

### Security

- **Token hashing**: Plain tokens never stored, only SHA256 hash
- **Domain validation**: Server checks `Origin`/`Referer` header against `allowed_domains`
- **Rate limiting**: Per-token, configurable (default 30/min)
- **No org tools**: Embedded agents only get KnowledgeSearchTool, not ModuleToolRegistry tools
- **Credits**: Billed to agent creator's org (the org that owns the agent)
- **CORS**: Dynamic CORS headers based on token's `allowed_domains`

### Plan-Gating

Requires `bot_studio_embed` feature flag (platform-enterprise plan).

---

## Implementation Order

| # | What | Spec | Effort |
|---|------|------|--------|
| 1 | Marketplace migrations + models | B | Small |
| 2 | MarketplaceController + routes | B | Small |
| 3 | Marketplace React pages | B | Medium |
| 4 | Publish flow (edit page Settings tab) | B | Small |
| 5 | Embed migrations + models + EmbedTokenService | C | Small |
| 6 | EmbedApiController + routes | C | Medium |
| 7 | Widget JS bundle (Vite entry + shadow DOM) | C | Medium |
| 8 | Embed tab on edit page | C | Small |
| 9 | Standalone chat page | C | Small |
| 10 | Plan-gating for both | B+C | Small |

---

## Acceptance Criteria

### Marketplace (Spec B)

- [ ] Marketplace page at `/marketplace` with search, filter, sort
- [ ] Featured agents section (is_featured = true)
- [ ] Agent detail page with rating breakdown and reviews
- [ ] Install creates local copy + agent_installs record
- [ ] Rating/review system (1-5 stars + text, one per user)
- [ ] Counter caches updated on install/review
- [ ] Publish toggle on agent edit page (requires bot_studio_pro)
- [ ] Category selection for published agents
- [ ] Cannot review own agent
- [ ] Installed agents show "Installed" badge

### Embed Widget (Spec C)

- [ ] Embed toggle on agent edit page
- [ ] Token management (create/revoke/rename)
- [ ] Domain whitelist per token
- [ ] Theme customization (color, position, greeting, placeholder)
- [ ] Embed code generator with copy button
- [ ] Widget JS loads via `<script>` tag on external sites
- [ ] Shadow DOM for style isolation
- [ ] NDJSON streaming chat in widget
- [ ] Token-based auth (no user session needed)
- [ ] Rate limiting per token
- [ ] Credits billed to agent creator's org
- [ ] No org tools in embedded mode (knowledge/RAG only)
- [ ] Standalone chat page at `/chat/{slug}`
- [ ] Requires bot_studio_embed feature flag
