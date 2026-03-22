<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string $slug
 * @property array<string, mixed>|null $puck_json
 * @property bool $is_published
 * @property string|null $meta_description
 * @property string|null $meta_image
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PageRevision> $revisions
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $extra_attributes
 */
final class Page extends Model
{
    use BelongsToOrganization;
    use HasSlug;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use SchemalessAttributesTrait;

    /** @var list<string> */
    protected $schemalessAttributes = ['extra_attributes'];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'puck_json',
        'is_published',
        'meta_description',
        'meta_image',
    ];

    /**
     * Generate a unique slug, appending -N suffix if needed.
     */
    public static function generateUniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base;
        $n = 1;
        $query = self::query()->where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $base.'-'.($n++);
            $query = self::query()->where('slug', $slug);

            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($query) => $query->where('organization_id', $this->organization_id))
            ->skipGenerateWhen(fn (): bool => ! empty($this->slug));
    }

    /**
     * @return HasMany<PageRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\PageBuilder\Database\Factories\PageFactory
    {
        return \Modules\PageBuilder\Database\Factories\PageFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'puck_json' => 'array',
            'is_published' => 'boolean',
        ];
    }
}
