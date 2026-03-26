---
title: Create Resources and Resource Templates
impact: MEDIUM
impactDescription: Expose data and documents for AI client context
tags: resource, create, template, uri, mime, blob
---

## Create Resources and Resource Templates

**Impact: MEDIUM (Expose data and documents for AI client context)**

Resources provide data that AI clients read as context. Static resources have fixed URIs; resource templates use URI patterns with variables for dynamic content.

## Bad Example

```php
// Exposing data through tools instead of resources
// Tools are for actions — resources are for data
class GetGuidelinesTool extends Tool
{
    public function handle(Request $request): Response
    {
        // This is read-only data, not an action — should be a resource
        return Response::text(file_get_contents(storage_path('guidelines.md')));
    }
}
```

## Good Example

```php
// php artisan make:mcp-resource WeatherGuidelinesResource

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Description('Comprehensive guidelines for using the Weather API.')]
#[Uri('weather://resources/guidelines')]
#[MimeType('text/plain')]
class WeatherGuidelinesResource extends Resource
{
    public function handle(Request $request): Response
    {
        $guidelines = file_get_contents(storage_path('weather/guidelines.md'));

        return Response::text($guidelines);
    }
}
```

```php
// Register in server
class WeatherServer extends Server
{
    protected array $resources = [
        WeatherGuidelinesResource::class,
    ];
}
```

```php
// Resource template — dynamic URIs with variables
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Support\UriTemplate;

#[Description('Access user files by ID')]
#[MimeType('text/plain')]
class UserFileResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('file://users/{userId}/files/{fileId}');
    }

    public function handle(Request $request): Response
    {
        $userId = $request->get('userId');
        $fileId = $request->get('fileId');

        $content = Storage::get("users/{$userId}/files/{$fileId}");

        return Response::text($content);
    }
}
```

```php
// Blob response for binary content (images, PDFs)
#[MimeType('image/png')]
class WeatherRadarResource extends Resource
{
    public function handle(Request $request): Response
    {
        return Response::blob(file_get_contents(storage_path('weather/radar.png')));
    }
}
```

```php
// Resource annotations
use Laravel\Mcp\Enums\Role;
use Laravel\Mcp\Server\Annotations\Audience;
use Laravel\Mcp\Server\Annotations\LastModified;
use Laravel\Mcp\Server\Annotations\Priority;

#[Audience(Role::User)]
#[LastModified('2026-01-12T15:00:58Z')]
#[Priority(0.9)]
class UserDashboardResource extends Resource { /* ... */ }
```

```php
// Conditional registration
class PremiumDataResource extends Resource
{
    public function shouldRegister(Request $request): bool
    {
        return $request?->user()?->subscribed() ?? false;
    }
}
```

## Why

- **Semantic**: Resources are data, tools are actions — AI clients understand the difference
- **Templates**: URI patterns enable dynamic resources without defining each one individually
- **Typed**: MIME type tells AI clients how to interpret the content
- **Annotated**: Priority and audience hints help AI clients choose relevant resources
- **Conditional**: Show resources based on auth state or subscription

Reference: [Laravel MCP — Resources](https://laravel.com/docs/13.x/mcp#resources)
