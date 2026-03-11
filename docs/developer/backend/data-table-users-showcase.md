# Data Table – Users Table Showcase

The **users table** (`/users`) is the main showcase for **machour/laravel-data-table**. This doc lists what is demonstrated there and what **cannot** be shown (and why).

**DataTable feature index (all real pages):** Users (`/users`), Announcements (`/announcements`), Posts (`/posts`), Organizations (`/organizations/list`), Categories (`/categories`). Row **reorder** is demonstrated on **Announcements** (position column). **AI** (NLQ, insights, suggest, column summary, enrich, Thesys Visualize) is on **Users** via `HasAi`, `DataTableAiController`, and the Users page props `aiBaseUrl` and `aiThesys`.

## What we do show

- **Inline editing** – Name and email are editable (double‑click → edit → PATCH). `HasInlineEdit`, `tableInlineEditModel`, `tableInlineEditRules`, `handleInlineEdit`, audit log.
- **AI panel** – Users table has `HasAi`; frontend passes `aiBaseUrl` and `aiThesys` from server props for NLQ, insights, suggestions, column summary, enrich, and Thesys Visualize (requires `THESYS_API_KEY` in `.env`; see `config('services.thesys.api_key')`).
- **Row expansion (detail row)** – Expand/drawer shows extra fields (email_verified_at, updated_at, organizations_count). `tableDetailRowEnabled`, `tableDetailDisplay` = `'drawer'`, `tableDetailRow(model)`.
- **Relational-style data** – `organizations_count` (withCount) and optionally “First org” name from the first related organization. Not the package’s dot-notation `relation` + `internalName` (that fits single relations like belongsTo); we show a **computed** relation value (count + first org name) on a many-to-many.
- **Other features** – Sorting, filtering, enum/async filters, quick views, soft deletes, summary/footer, badge/row index/prefix/tooltip/suffix, export/import, toggle, select-all, polling, defer load, group by, authorization, persist state, row/bulk actions (including Duplicate, Send message with form), and all table options (resize, pin, batch edit, printable, etc.).

## Features we cannot show on the users table (and why)

| Feature | Why we can’t show it here |
|--------|----------------------------|
| **Row reorder (HasReorder)** | User has no `position` / `sort_order` column. Reorder is demonstrated on **Announcements** (`/announcements`) instead. |
| **Cascading filters** | Requires two filters where one’s options depend on the other (e.g. Country → City). Users have no such parent/child pair in the schema. |
| **Cursor pagination** | Could be enabled, but we use standard pagination as the main example. Cursor changes URL shape and “total” semantics. |
| **Simple pagination** | Same: we could switch, but we showcase standard (with total count). |
| **Column types: currency, percentage, phone, link (to external), color** | User model has no price, percentage, phone, or color field; no “external link” column. |
| **Column type: select (inline dropdown edit)** | We use badge for status. We could add a select column for status, but badge + enum filter is already the demo. |
| **Stacked column** | We could stack name+email in one cell; we keep them separate for clarity. |
| **HTML / Markdown / bulleted** | No user field we display as HTML/Markdown or as a bullet list. |
| **Laravel Echo / real-time** | Would require Echo + broadcast setup and table subscribing to events; not part of the users demo. |
| **Backend saved views (DB)** | Package supports DB-backed saved views (migrations + `SavedViewController`). We didn’t wire it to the users table (no `savedViewsUrl` in response); frontend uses **localStorage** for custom quick views only. So “could add, not shown”. |
| **Virtual scrolling** | Could be enabled if the DataTable component supports it; we didn’t enable it. |
| **Queued export + export-status polling** | We use direct export. Queued + status endpoint could be added but isn’t in the current showcase. |
| **PDF export** | Would need `barryvdh/laravel-dompdf` and export config; not enabled for users. |
| **Package built-in Force-delete / Restore** | We have custom bulk soft-delete and “With trashed” / “Only trashed” quick views. We didn’t add the package’s built-in force-delete/restore row actions (could add). |
| **Inline row creation (“Add row”)** | We have “Add user” as a header action (navigate to create page). The package’s inline “Add row” in the table isn’t wired for users. |
| **Batch inline edit (change one column for all selected)** | `batchEdit: true` is in options; the actual “edit column X for selected rows” flow may or may not be implemented in the React component. |
| **Date grouping (group by day/week/month)** | We group by `onboarding_completed`. Date grouping (e.g. by created_at month) is a different feature; not enabled for users. |
| **User-selectable group-by column** | We use a fixed `tableGroupByColumn()`. Letting the user pick the group-by column from a dropdown would require passing `groupByOptions` and a backend param; not implemented. |

## Relational data vs users table

- **Package “relational”** = column with `relation` + `internalName` (e.g. `relation: 'user'`, `internalName: 'user.name'`). Suited to **single** relations (belongsTo, hasOne). The package eager-loads and maps the value from the related model.
- **User ↔ Organization** is **many-to-many**. There is no single “the organization” for a user, so we can’t use one `relation` + `internalName` column for “organization name”.
- **What we do**:
  - **organizations_count** – aggregate from `withCount('organizations')`; shows “how many orgs” (relational aggregate).
  - **First org name** (if added) – DTO property filled in `fromModel()` from `$model->organizations->first()?->name`; shows “related” data from the first organization only. This demonstrates “show a related model’s attribute” even though it’s not the package’s automatic relation column.

So: **inline editing** and **row expansion** are shown; **relational data** is shown as count + (optionally) first org name; the package’s **dot-notation relation column** is not used on users because of the many-to-many shape.
