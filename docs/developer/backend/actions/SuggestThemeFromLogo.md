# SuggestThemeFromLogo

## Purpose

Analyzes an uploaded logo image using Gemini vision (via Prism) and returns a suggested Tailux theme configuration based on the logo's dominant colors and brand personality.

## Location

`app/Actions/SuggestThemeFromLogo.php`

## Method Signature

```php
public function handle(UploadedFile $file): array
```

## Dependencies

None (no constructor — uses `Prism` facade directly).

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$file` | `UploadedFile` | The uploaded logo image (jpg, jpeg, png, gif, or webp) |

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

## AI Model

- **Provider**: Gemini (via Prism structured output)
- **Model**: `gemini-1.5-flash`
- **Input**: Base64-encoded image + text prompt
- **Output**: Validated structured object via Prism `ObjectSchema`

## Error Handling

Wrapped in `try/catch`. On **any** failure (Gemini key not configured, API timeout, schema validation error) the action silently returns the Corporate preset fallback:

```php
['dark' => 'navy', 'primary' => 'indigo', 'light' => 'slate', 'skin' => 'shadow',
 'radius' => 'default', 'font' => 'inter', 'menuColor' => 'default', 'menuAccent' => 'subtle',
 'reason' => 'Default theme applied.']
```

## Usage Example

```php
// From OrgThemeController::analyzeLogo()
$suggestion = $action->handle($request->file('logo'));
// $suggestion['primary'] => 'rose', $suggestion['reason'] => 'Warm brand with playful curves'
```

## Related Components

- **Controller**: `OrgThemeController::analyzeLogo()`
- **Route**: `org.theme.analyze-logo` (POST `/org/theme/analyze-logo`)
- **Frontend**: `resources/js/components/ui/theme-customizer.tsx` — `handleLogoUpload` callback
- **Config**: Gemini API key via Prism provider configuration
