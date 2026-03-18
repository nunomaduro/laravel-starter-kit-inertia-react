<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TermsType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $body
 * @property TermsType $type
 * @property \Carbon\CarbonImmutable $effective_at
 * @property string|null $summary
 * @property bool $is_required
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserTermsAcceptance> $acceptances
 */
final class TermsVersion extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'type',
        'effective_at',
        'summary',
        'is_required',
    ];

    /**
     * @return HasMany<UserTermsAcceptance>
     */
    public function acceptances(): HasMany
    {
        return $this->hasMany(UserTermsAcceptance::class, 'terms_version_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'effective_at' => 'date',
            'is_required' => 'boolean',
            'type' => TermsType::class,
        ];
    }
}
