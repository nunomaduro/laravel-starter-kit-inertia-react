# GeneratePdf

## Purpose

{One-line description of what this action does}

## Location

`app/Actions/GeneratePdf.php`

## Method Signature

```php
public function handle({parameters}): {returnType}
```

## Dependencies

{List injected dependencies from constructor, or "None" if no dependencies}

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| {param} | {type} | {description} |

## Return Value

{Description of what the method returns}

## Usage Examples

### From Controller

```php
app(GeneratePdf::class)->handle($params);
```

### From Job/Command

```php
(new GeneratePdf($dependency))->handle($params);
```

## Related Components

- **Controller**: `{RelatedController}` (if applicable)
- **Route**: `{RouteName}` ({HttpMethod} {RoutePath}) (if applicable)
- **Model**: `{RelatedModel}` (if applicable)

## Notes

{Any additional notes, edge cases, or important information}
