---
name: visibility-sharing
description: >-
  Visibility and cross-organization sharing with HasVisibility. Activates when
  working with HasVisibility trait, VisibilityEnum, Shareable, VisibilityScope,
  shareItem policy, or copy-on-write cloning.
---

# Visibility & Sharing Skill

## When to Activate

This skill activates when:

- Adding or editing models that use (or should use) **HasVisibility**
- Working with **visibility levels** (global / organization / shared)
- Implementing or changing **cross-organization sharing** (Shareable, shareWithOrganization, shareWithUser)
- Editing **VisibilityScope**, **VisibilityEnum**, **ShareablePolicy**, or **Shareable** model
- Implementing **copy-on-write** (`cloneForOrganization`) or share revoke behavior
- User mentions: visibility, sharing, share with org, HasVisibility, Shareable, global visibility

## Key Reference

- **Doc:** `docs/developer/backend/visibility-sharing.md`
- **Trait:** `App\Models\Concerns\HasVisibility`
- **Do not** use `BelongsToOrganization` on the same model as `HasVisibility`; the trait applies `VisibilityScope` and owns the organization relationship.
- **Columns:** `organization_id` (nullable), `visibility` (string), optionally `cloned_from`
- **Authorization:** `shareItem` ability (ShareablePolicy) — user must be able to edit the shareable (e.g. org admin or shared with edit)

## Reference Implementation

- `App\Models\VisibilityDemo` — minimal model using HasVisibility
- `tests/Feature/HasVisibilityTest.php` — scope, sharing, revoke, clone, canBeViewedBy/canBeEditedBy
