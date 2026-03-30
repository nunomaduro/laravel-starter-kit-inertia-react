# Categorizable (nested set categories)

Models can be attached to a **tree of categories** using **kalnoy/nestedset** and the app’s **Categorizable** trait. Categories are stored in `categories` (nested set: `_lft`, `_rgt`, `parent_id`) and the polymorphic pivot `categoryables` links categories to models.

## Models and trait

- **Category** (`App\Models\Category`): uses `NodeTrait` (nested set) and `HasSlug` (slug from name). Fillable: `name`, `slug`, `type`, `parent_id`.
- **Categorizable** (`App\Models\Concerns\Categorizable`): use on any model to get `categories()` (morphToMany), `attachCategory()`, `detachCategory()`, `syncCategories()`, `hasCategory()`, `categoriesList()`, `categoriesIds()`.

## User

The **User** model uses the **Categorizable** trait, so users can be assigned to categories (e.g. segments or groups). Assign categories in code via `$user->attachCategory($category)` / `$user->syncCategories([...])`.

## Category Management

- **Categories page**: Manage categories (name, slug, type, parent) at `/categories` via `CategoriesTableController`. Create/edit categories and set parent for tree structure.
- **User categories**: Users can have categories attached via the Categorizable relationship — manage the category tree at `/categories`.

## Coexistence with Spatie Tags

- **Tags** (spatie/laravel-tags): flat list of tags on User (or other models).
- **Categories** (this feature): tree of categories; models can have many categories via the Categorizable trait.

Use tags for ad-hoc labels and categories for hierarchical grouping (e.g. user segments, content taxonomy).

## References

- [kalnoy/nestedset](https://github.com/lazychaser/laravel-nestedset) — nested set implementation.
