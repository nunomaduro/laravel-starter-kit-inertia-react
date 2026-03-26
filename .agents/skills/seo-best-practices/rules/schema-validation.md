---
title: Structured Data Validation and Monitoring
impact: HIGH
impactDescription: "Invalid schema silently fails — broken markup gets zero rich results"
tags: validation, rich-results-test, search-console, monitoring
---

## Structured Data Validation and Monitoring

**Impact: HIGH (Invalid schema silently fails — broken markup gets zero rich results)**

Structured data errors are completely silent — there is no browser console warning when your JSON-LD has a missing required field or a trailing comma. The only way to catch issues is through proactive validation and monitoring. Without a validation workflow, broken schema can go undetected for months while competitors capture your rich result slots.

## Incorrect

```php
// ❌ Deploying schema without any validation or monitoring
class BlogPostController extends Controller
{
    public function show(Post $post)
    {
        // Schema built inline, never tested, never validated
        return Inertia::render('Blog/Show', [
            'post' => $post,
            // No one checks if this produces valid JSON-LD
            // No tests verify schema presence
            // Search Console errors go unchecked for months
        ]);
    }
}
```

**Problems:**
- No automated tests verify that JSON-LD is present and parseable on rendered pages
- No pre-deployment validation catches missing required fields or syntax errors
- Search Console enhancement reports are never reviewed — errors accumulate silently
- A template change can break schema across hundreds of pages with no alert

## Correct

### 1. Validate Before Deploying

Always test structured data with Google's Rich Results Test before deploying changes. Paste the rendered HTML or a live URL to verify all schema types are detected and have no errors or warnings.

### 2. Write Automated Tests

```php
// ✅ Laravel feature test verifying Article schema is present and valid
namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_post_has_valid_article_schema(): void
    {
        $post = Post::factory()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertStatus(200);
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('"headline"', false);
    }

    public function test_blog_post_schema_contains_required_fields(): void
    {
        $author = User::factory()->create(['name' => 'Jane Smith']);
        $post = Post::factory()->create([
            'title' => 'Test Post Title',
            'user_id' => $author->id,
        ]);

        $response = $this->get(route('posts.show', $post));
        $content = $response->getContent();

        // Extract JSON-LD blocks from rendered HTML
        preg_match_all(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $content,
            $matches
        );

        $this->assertNotEmpty($matches[1], 'No JSON-LD blocks found on page');

        $hasArticle = false;
        foreach ($matches[1] as $jsonLd) {
            $data = json_decode($jsonLd, true);
            $this->assertNotNull($data, 'JSON-LD block is not valid JSON');

            // Check @graph array or single type
            $entities = isset($data['@graph']) ? $data['@graph'] : [$data];

            foreach ($entities as $entity) {
                if (($entity['@type'] ?? '') === 'Article') {
                    $hasArticle = true;
                    $this->assertArrayHasKey('headline', $entity);
                    $this->assertArrayHasKey('datePublished', $entity);
                    $this->assertArrayHasKey('author', $entity);
                    $this->assertArrayHasKey('image', $entity);
                    $this->assertEquals('Test Post Title', $entity['headline']);
                }
            }
        }

        $this->assertTrue($hasArticle, 'No Article schema found in JSON-LD');
    }

    public function test_faq_page_has_valid_faq_schema(): void
    {
        $response = $this->get(route('faq.index'));

        $response->assertStatus(200);
        $response->assertSee('"@type":"FAQPage"', false);
        $response->assertSee('"mainEntity"', false);
        $response->assertSee('"acceptedAnswer"', false);
    }
}
```

### 3. Monitor Search Console Weekly

Check Google Search Console **Enhancements** reports at least weekly for schema errors:

- Navigate to **Search Console > Enhancements** and review each structured data type
- Look for **Error** and **Warning** counts — both prevent rich results
- Click into specific issues to see affected URLs
- After fixing, use **Validate Fix** to request re-crawling

### 4. Common Validation Errors

| Error | Cause | Fix |
|---|---|---|
| Missing required field `image` | Article schema without image property | Add at least one image URL to the `image` array |
| Invalid date format | Using `"March 10, 2026"` instead of ISO 8601 | Use `"2026-03-10T08:00:00+00:00"` or `->toIso8601String()` |
| URL is not absolute | Using `/blog/my-post` instead of full URL | Use `url()` or `route()` helpers to generate absolute URLs |
| Content mismatch | Schema `headline` differs from visible `<h1>` | Ensure schema values match on-page content exactly |
| Trailing comma in JSON | `{"name": "Acme",}` — invalid JSON syntax | Use `json_encode()` in PHP or `JSON.stringify()` in JS instead of manual strings |
| `author` is a string | `"author": "Jane"` instead of a Person object | Use `{"@type": "Person", "name": "Jane"}` |
| Missing `@context` | JSON-LD without `"@context": "https://schema.org"` | Always include `@context` at the top level or in `@graph` wrapper |

### 5. CI Pipeline Validation

```php
// ✅ Base test helper for reusable JSON-LD validation
namespace Tests;

use Illuminate\Testing\TestResponse;

trait ValidatesJsonLd
{
    /**
     * Assert that a response contains valid JSON-LD with the given @type.
     */
    protected function assertHasJsonLdType(TestResponse $response, string $type): array
    {
        $content = $response->getContent();

        preg_match_all(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $content,
            $matches
        );

        $this->assertNotEmpty($matches[1], 'No JSON-LD blocks found');

        foreach ($matches[1] as $jsonLd) {
            $data = json_decode($jsonLd, true);
            $this->assertNotNull($data, "Invalid JSON in JSON-LD block: {$jsonLd}");

            $entities = isset($data['@graph']) ? $data['@graph'] : [$data];

            foreach ($entities as $entity) {
                if (($entity['@type'] ?? '') === $type) {
                    return $entity;
                }
            }
        }

        $this->fail("No JSON-LD entity with @type \"{$type}\" found");
    }
}
```

```tsx
// ✅ React: verify schema is rendered correctly in development
import { Head } from '@inertiajs/react';

interface SchemaScriptProps {
  schema: Record<string, unknown>;
}

export function SchemaScript({ schema }: SchemaScriptProps) {
  const jsonString = JSON.stringify(schema);

  // Validate in development — catch issues before they reach production
  if (import.meta.env.DEV) {
    try {
      const parsed = JSON.parse(jsonString);
      if (!parsed['@context'] && !parsed['@graph']) {
        console.warn('[Schema] Missing @context in JSON-LD:', parsed);
      }
    } catch {
      console.error('[Schema] Invalid JSON-LD:', jsonString);
    }
  }

  return (
    <Head>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: jsonString }}
      />
    </Head>
  );
}
```

**Benefits:**
- Automated tests catch schema regressions on every pull request before they reach production
- The `ValidatesJsonLd` trait makes it easy to add schema assertions to any feature test
- Weekly Search Console monitoring catches issues that automated tests cannot (e.g., crawl-time rendering differences)
- Development-mode validation in React gives immediate console feedback during local development

Reference: [Google Rich Results Test](https://search.google.com/test/rich-results)
