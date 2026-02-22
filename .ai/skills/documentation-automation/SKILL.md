---
name: documentation-automation
description: >-
  Automates documentation when features are added or modified. Activates when
  creating Actions, Controllers, Pages, Routes, or Models; when modifying
  config/fortify.php; or when user mentions docs, documentation, readme.
---

# Documentation Automation Skill

## When to Activate

This skill activates when:

- Creating new Actions in `app/Actions/`
- Adding new Controllers or routes
- Creating Inertia pages in `resources/js/pages/`
- Modifying `config/fortify.php` features
- Adding new Eloquent models
- Creating Data objects (app/Data/) — see docs/developer/backend/search-and-data.md
- User explicitly requests documentation
- User mentions: docs, documentation, readme, document this

## Required Boost Tools

Before generating documentation, ALWAYS gather context using Boost MCP tools:

| Tool | When to Use | What It Provides |
|------|-------------|-------------------|
| `application-info` | Every documentation task | Models, packages, PHP version, Eloquent models |
| `database-schema` | When documenting models/data | Table structure, relationships, foreign keys |
| `list-routes` | When documenting controllers/pages | All routes with parameters, middleware |
| `search-docs` | When documenting Laravel features | Official patterns and examples for your package versions |

## Documentation Process

### Step 1: Detect What Changed

Identify the change type:
- **New Action**: `app/Actions/NewAction.php`
- **New Controller**: `app/Http/Controllers/NewController.php`
- **New Page**: `resources/js/pages/new-page.tsx`
- **New Route**: Addition to `routes/web.php`
- **Schema Change**: New migration or model change

### Step 2: Gather Context with Boost Tools

<code-snippet name="Context Gathering" lang="text">
1. Call `application-info` to get:
   - Related models and their relationships
   - Package versions (for accurate docs)
   - Application context
   
2. Call `list-routes` with filter if relevant:
   - Get route parameters
   - Identify middleware
   - Find related routes
   
3. Call `database-schema` if data-related:
   - Table structure
   - Foreign keys
   - Column types
   
4. Call `search-docs` for Laravel-specific features:
   - Version-specific documentation
   - Best practices
</code-snippet>

### Step 3: Determine Documentation Scope

| Change | User Guide | Developer Guide | API Reference |
|--------|------------|----------------|--------------|
| New user feature | ✅ Create | ✅ Create | ✅ Update routes |
| New Action | ❌ | ✅ Create | ❌ |
| New Controller | ❌ | ✅ Create | ✅ Update routes |
| Bug fix | ❌ | ❌ | ❌ |
| New API endpoint | ❌ | ✅ Create | ✅ Create |
| UI refactor | ✅ If flow changes | ❌ | ❌ |

### Step 4: Generate Documentation

Use templates from `docs/.templates/` based on component type:

#### Action Documentation Template

<code-snippet name="Action Doc Template" lang="markdown">
# {ActionName}

## Purpose
{One-line description of what this action does}

## Location
`app/Actions/{ActionName}.php`

## Method Signature
```php
public function handle({parameters}): {returnType}
```

## Dependencies
{List injected dependencies from constructor, or "None"}

## Usage Examples

### From Controller
```php
app({ActionName}::class)->handle($params);
```

## Related
- Controller: `{RelatedController}` (if applicable)
- Route: `{RouteName}` ({HttpMethod} {RoutePath}) (if applicable)
</code-snippet>

#### Page Documentation Template

<code-snippet name="Page Doc Template" lang="markdown">
# {PageName}

## Purpose
{What this page allows users to do}

## Location
`resources/js/pages/{path}.tsx`

## Route
- **URL**: `{url}`
- **Name**: `{routeName}`
- **Middleware**: {middleware}

## Props (from Controller)
| Prop | Type | Description |
|------|------|-------------|
| {prop} | {type} | {description} |

## User Flow
1. User navigates to {url}
2. {Step 2}
3. {Step 3}

## Related
- Controller: `{Controller}@{method}`
- Action: `{ActionName}` (if applicable)
</code-snippet>

### Step 5: Update Documentation Manifest

After creating documentation, update `docs/.manifest.json`:

<code-snippet name="Manifest Update" lang="json">
{
  "actions": {
    "CreateUser": {
      "documented": true,
      "path": "docs/developer/backend/actions/create-user.md",
      "lastUpdated": "2026-01-27"
    }
  }
}
</code-snippet>

## Integration with Existing Skills

This skill works alongside:

- **pest-testing**: When tests are written, documentation should reflect tested behavior
- **inertia-react-development**: Page documentation should include Inertia props and patterns
- **wayfinder-development**: Route documentation should use Wayfinder function names

## Common Pitfalls

- Forgetting to use `application-info` before documenting models
- Not checking `list-routes` for accurate route information
- Skipping `search-docs` when documenting Laravel-specific features
- Not updating the manifest after creating documentation
- Not updating index/README files when adding new documentation

## Documentation Locations

- **Search & Data**: `docs/developer/backend/search-and-data.md` — DTOs, Sluggable, Sortable, Model Flags, Schemaless Attributes, Model States, Soft Cascade
- **Actions**: `docs/developer/backend/actions/{action-name}.md`
- **Controllers**: `docs/developer/backend/controllers/{controller-name}.md`
- **Pages**: `docs/developer/frontend/pages/{page-name}.md`
- **User Features**: `docs/user-guide/{category}/{feature-name}.md`
- **Routes**: `docs/developer/api-reference/routes.md`
