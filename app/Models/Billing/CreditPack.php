<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Database\Factories\Billing\CreditPackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $credits
 * @property int $bonus_credits
 * @property int $price
 * @property string $currency
 * @property int|null $validity_days
 * @property bool $is_active
 * @property int $sort_order
 */
final class CreditPack extends Model implements Sortable
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;
    use SortableTrait;

    /** @var array<string, mixed> */
    public array $sortable = [
        'order_column_name' => 'sort_order',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'credits',
        'bonus_credits',
        'price',
        'currency',
        'validity_days',
        'is_active',
        'sort_order',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    protected static function newFactory(): CreditPackFactory
    {
        return CreditPackFactory::new();
    }

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'bonus_credits' => 'integer',
            'price' => 'integer',
            'validity_days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
