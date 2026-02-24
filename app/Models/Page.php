<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property-read Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PageRevision> $revisions
 */
final class Page extends Model
{
    use BelongsToOrganization;
    use HasSlug;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

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
