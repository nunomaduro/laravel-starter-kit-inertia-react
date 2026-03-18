<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int|null $organization_id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property int $_lft
 * @property int $_rgt
 * @property int|null $parent_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
final class Category extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasSlug;
    use NodeTrait;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'parent_id',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->allowDuplicateSlugs();
    }

    /**
     * @return BelongsToMany<Model, $this>
     */
    public function entries(string $modelClass): BelongsToMany
    {
        return $this->morphedByMany($modelClass, 'categoryable', 'categoryables');
    }
}
