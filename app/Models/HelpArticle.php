<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\Categorizable;
use Database\Factories\HelpArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Mattiverse\Userstamps\Traits\Userstamps;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;

/**
 * @property int $id
 * @property int|null $organization_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $content
 * @property string|null $category
 * @property int $views
 * @property int $helpful_count
 * @property int $not_helpful_count
 * @property int $order
 * @property bool $is_published
 * @property bool $is_featured
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
final class HelpArticle extends Model implements HasMedia, Sortable
{
    /** @use HasFactory<HelpArticleFactory> */
    use BelongsToOrganization;

    use Categorizable;
    use HasFactory;
    use HasSlug;
    use HasTags;
    use InteractsWithMedia;
    use LogsActivity;
    use Searchable;
    use SoftDeletes;
    use SortableTrait;
    use Userstamps;

    /**
     * @var array<string, string>
     */
    public array $sortable = [
        'order_column_name' => 'order',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'views',
        'helpful_count',
        'not_helpful_count',
        'order',
        'is_published',
        'is_featured',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt ?? '',
            'content' => strip_tags((string) $this->content),
            'category' => $this->category ?? '',
            'created_at' => $this->created_at?->timestamp ?? 0,
            'updated_at' => $this->updated_at?->timestamp ?? 0,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'category', 'is_published', 'is_featured', 'order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('help_article');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'helpful_count' => 'integer',
            'not_helpful_count' => 'integer',
            'order' => 'integer',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    #[Scope]
    protected function featured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
