---
title: XML Sitemap Best Practices
impact: HIGH
impactDescription: "Helps search engines discover and index all pages"
tags: sitemap, indexing, technical-seo
---

## XML Sitemap Best Practices

**Impact: HIGH (Helps search engines discover and index all pages)**

An XML sitemap is a roadmap for search engines, listing every page you want indexed along with metadata about when it was last updated. A well-maintained sitemap improves crawl efficiency, ensures new content is discovered quickly, and prevents important pages from being missed.

## Incorrect

```xml
<!-- ❌ Bad: includes noindex pages, stale dates, bloated sitemap -->
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- noindex page should not be in sitemap -->
  <url>
    <loc>https://example.com/admin/dashboard</loc>
    <lastmod>2020-01-01</lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Stale lastmod date from years ago -->
  <url>
    <loc>https://example.com/products/widget</loc>
    <lastmod>2019-06-15</lastmod>
    <changefreq>always</changefreq>
    <priority>0.9</priority>
  </url>

  <!-- Non-canonical URL variation -->
  <url>
    <loc>https://example.com/products/widget?ref=homepage</loc>
    <lastmod>2019-06-15</lastmod>
  </url>

  <!-- 50,000+ URLs in a single file makes it slow to parse -->
</urlset>
```

**Problems:**
- Including noindex or admin pages in the sitemap sends contradictory signals to crawlers
- Stale `lastmod` dates cause crawlers to skip pages that may have been updated
- Non-canonical URL variations waste crawl budget and dilute link signals
- A single sitemap file with over 50,000 URLs exceeds the sitemap protocol limit
- `priority` and `changefreq` are largely ignored by Google and add noise

## Correct

```xml
<!-- ✅ Good: clean sitemap with only canonical, indexable URLs -->
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url>
    <loc>https://example.com/</loc>
    <lastmod>2026-03-10</lastmod>
  </url>

  <url>
    <loc>https://example.com/products/wireless-headphones</loc>
    <lastmod>2026-03-08</lastmod>
    <image:image>
      <image:loc>https://example.com/images/wireless-headphones.webp</image:loc>
      <image:title>Wireless Noise-Cancelling Headphones</image:title>
    </image:image>
  </url>

  <url>
    <loc>https://example.com/blog/seo-guide-2026</loc>
    <lastmod>2026-02-20</lastmod>
  </url>
</urlset>
```

```xml
<!-- ✅ Good: sitemap index for large sites (>50k URLs) -->
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>https://example.com/sitemaps/pages.xml</loc>
    <lastmod>2026-03-10</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemaps/products-001.xml</loc>
    <lastmod>2026-03-08</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemaps/products-002.xml</loc>
    <lastmod>2026-03-05</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemaps/blog.xml</loc>
    <lastmod>2026-02-20</lastmod>
  </sitemap>
</sitemapindex>
```

```php
// ✅ Laravel: using spatie/laravel-sitemap
// Install: composer require spatie/laravel-sitemap

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;
use App\Models\Post;

// app/Console/Commands/GenerateSitemap.php
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    public function handle(): void
    {
        $sitemapIndex = SitemapIndex::create();

        // Products sitemap
        $productSitemap = Sitemap::create();
        Product::query()
            ->where('is_published', true)
            ->where('is_indexable', true)
            ->cursor()
            ->each(function (Product $product) use ($productSitemap) {
                $productSitemap->add(
                    Url::create(route('products.show', $product->slug))
                        ->setLastModificationDate($product->updated_at)
                        ->addImage($product->image_url, $product->name)
                );
            });
        $productSitemap->writeToFile(public_path('sitemaps/products.xml'));
        $sitemapIndex->add('/sitemaps/products.xml');

        // Blog sitemap
        $blogSitemap = Sitemap::create();
        Post::query()
            ->where('status', 'published')
            ->cursor()
            ->each(function (Post $post) use ($blogSitemap) {
                $blogSitemap->add(
                    Url::create(route('blog.show', $post->slug))
                        ->setLastModificationDate($post->updated_at)
                );
            });
        $blogSitemap->writeToFile(public_path('sitemaps/blog.xml'));
        $sitemapIndex->add('/sitemaps/blog.xml');

        // Write sitemap index
        $sitemapIndex->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');
    }
}
```

**Benefits:**
- Only canonical, indexable URLs are included, preventing wasted crawl budget
- Accurate `lastmod` dates from the database help crawlers prioritize recently updated content
- Image sitemap extension improves visibility in Google Image Search
- Sitemap index pattern keeps individual files under the 50,000 URL / 50MB limit
- Automated generation via commands or build steps ensures the sitemap stays current

Reference: [Google's Sitemap Documentation](https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview)
