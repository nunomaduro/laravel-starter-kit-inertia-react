# Visibility and Sharing (HasVisibility)

Models can support **visibility levels** and **cross-organization sharing** using the `HasVisibility` trait. This provides global / org / shared data with optional copy-on-write cloning.

## When to Use Which Trait

| Use Case | Trait | Notes |
|----------|-------|-------|
| Org-owned data only; no sharing | `BelongsToOrganization` | Simple org scoping; used by Post, Category, HelpArticle, Billing models |
| Global data (admin-created, visible to all orgs) | `HasVisibility` | Super-admin creates with `visibility=Global`, `organization_id=null` |
| Org data with optional sharing | `HasVisibility` | Default `visibility=Organization`; upgrade to `Shared` when sharing |
| Cross-org sharing with view/edit permissions | `HasVisibility` | `shareWithOrganization()`, `shareWithUser()` |

Do **not** use `BelongsToOrganization` and `HasVisibility` on the same model; `HasVisibility` owns the organization relationship.

## Visibility levels

- **Global** – Visible to all organizations (read-only). Only super-admins can create or set global visibility.
- **Organization** – Only visible to members of the owning organization (default).
- **Shared** – Visible to the owner org plus explicitly shared organizations or users (via `Shareable` records).

## Requirements

Models using `HasVisibility` must:

- Have an `organization_id` column (nullable for global items).
- Have a `visibility` column (string, default `'organization'`).
- Optionally have a `cloned_from` column for copy-on-write tracking.

Do **not** use `BelongsToOrganization` on the same model; `HasVisibility` applies `VisibilityScope` and owns the organization relationship.

## Usage

1. **Add columns** (migration): `organization_id` (nullable), `visibility` (string, default `'organization'`), optionally `cloned_from` (nullable).
2. **Use the trait** on the model: `use App\Models\Concerns\HasVisibility;`
3. **Reference model**: `App\Models\VisibilityDemo` is a minimal example used in tests.

## Key methods

- `shareWithOrganization(Organization|int $organization, string $permission = 'view', ?DateTimeInterface $expiresAt = null): Shareable`
- `shareWithUser(User|int $user, string $permission = 'view', ?DateTimeInterface $expiresAt = null): Shareable`
- `revokeOrganizationShare(Organization|int $organization): bool`
- `revokeUserShare(User|int $user): bool`
- `cloneForOrganization(Organization|int $organization): static` (copy-on-write)
- `canBeViewedBy(User $user): bool`, `canBeEditedBy(User $user): bool`
- `isGlobal()`, `isShared()`, `isOrgOnly()`

Sharing is authorized via the `shareItem` ability (ShareablePolicy): the user must be able to edit the shareable (e.g. admin of the owning organization or shared with edit permission).

## Related

- `App\Enums\VisibilityEnum` – Global, Organization, Shared
- `App\Models\Shareable` – Polymorphic share records (target: Organization or User; permission: view/edit; optional expiry)
- `App\Models\Scopes\VisibilityScope` – Global scope applied by the trait
- `config/tenancy.php` – `sharing` and `super_admin` settings
