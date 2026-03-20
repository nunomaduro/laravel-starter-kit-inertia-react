# SuggestThemeFromLogo

## Purpose

Analyzes an uploaded logo image using the `ThemeSuggestionAgent` (laravel/ai) and returns a suggested Tailux theme configuration based on the logo's dominant colors and brand personality.

## Location

`app/Actions/SuggestThemeFromLogo.php`

## Method Signature

```php
public function handle(UploadedFile $file, ?string $colorHint = null): array
```

## Dependencies

None (no constructor). Resolves `ThemeSuggestionAgent` directly.

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$file` | `UploadedFile` | The uploaded logo image (jpg, jpeg, png, gif, or webp) |
| `$colorHint` | `string\|null` | Optional dominant color hint extracted from the image (e.g. `'rose'`). Passed to the prompt so the agent can validate its visual analysis against a pre-extracted value. |

## Return Value

`array` with the following keys:

| Key | Type | Values |
|-----|------|--------|
| `dark` | `string` | `navy` \| `mirage` \| `mint` \| `black` \| `cinder` |
| `primary` | `string` | `indigo` \| `blue` \| `green` \| `amber` \| `purple` \| `rose` |
| `light` | `string` | `slate` \| `gray` \| `neutral` |
| `skin` | `string` | `shadow` \| `bordered` \| `flat` \| `elevated` |
| `radius` | `string` | `none` \| `sm` \| `default` \| `md` \| `lg` \| `full` |
| `font` | `string` | `inter` \| `geist-sans` \| `instrument-sans` \| `poppins` \| `outfit` \| `plus-jakarta-sans` |
| `menuColor` | `string` | `default` \| `primary` \| `muted` |
| `menuAccent` | `string` | `subtle` \| `strong` \| `bordered` |
| `reason` | `string` | Brief explanation of theme choices (≤100 characters) |
| `ai_derived` | `bool` | `true` when the response came from AI, `false` for the fallback |

## AI Agent

- **Agent**: `App\Ai\Agents\ThemeSuggestionAgent`
- **Provider**: OpenRouter (`#[Provider('openrouter')]`)
- **Model**: `google/gemini-2.0-flash-001` (`#[Model('google/gemini-2.0-flash-001')]`)
- **Input**: Uploaded image via `Image::fromUpload($file)` + text prompt
- **Output**: Structured array via `HasStructuredOutput` schema

The agent's schema enforces all enum values, so the response is always valid without additional validation.

## Error Handling

If `OPENROUTER_API_KEY` is not configured, or on any exception from the agent, the action silently returns the fallback preset:

```php
['dark' => 'navy', 'primary' => 'indigo', 'light' => 'slate', 'skin' => 'shadow',
 'radius' => 'default', 'font' => 'inter', 'menuColor' => 'default', 'menuAccent' => 'subtle',
 'reason' => 'Color extracted from your logo.', 'ai_derived' => false]
```

If `$colorHint` matches a valid primary color it is used as the fallback primary; otherwise `'indigo'` is used.

## Usage Example

```php
// From OrgThemeController::analyzeLogo()
$suggestion = $action->handle($request->file('logo'), colorHint: 'rose');
// $suggestion['primary'] => 'rose', $suggestion['ai_derived'] => true
```

## Related Components

- **Agent**: `app/Ai/Agents/ThemeSuggestionAgent.php`
- **Controller**: `OrgThemeController::analyzeLogo()`
- **Route**: `org.theme.analyze-logo` (POST `/org/theme/analyze-logo`)
- **Frontend**: `resources/js/components/ui/theme-customizer.tsx` — `handleLogoUpload` callback
- **Config**: OpenRouter API key via `OPENROUTER_API_KEY` / `PrismSettings`
