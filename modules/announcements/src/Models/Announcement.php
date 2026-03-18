<?php

declare(strict_types=1);

namespace Modules\Announcements\Models;

use App\Models\Organization;
use App\Models\User;
use GeneaLabs\LaravelGovernor\Traits\Governable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Announcements\Database\Factories\AnnouncementFactory;
use Modules\Announcements\Enums\AnnouncementLevel;
use Modules\Announcements\Enums\AnnouncementScope;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\ModelFlags\Models\Concerns\HasFlags;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property AnnouncementLevel $level
 * @property AnnouncementScope $scope
 * @property int|null $organization_id
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $ends_at
 * @property bool $is_active
 * @property int|null $position
 * @property int|null $created_by
 * @property int|null $governor_owned_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Organization|null $organization
 * @property-read User|null $creator
 * @property-read User|null $ownedBy
 */
final class Announcement extends Model implements Sortable
{
    use Governable;
    use HasFactory;
    use HasFlags;
    use SortableTrait;

    /** @var array<string, mixed> */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /** @var list<string> */
    protected $fillable = [
        'title',
        'body',
        'level',
        'scope',
        'organization_id',
        'starts_at',
        'ends_at',
        'is_active',
        'position',
        'created_by',
        'governor_owned_by',
    ];

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory(): AnnouncementFactory
    {
        return AnnouncementFactory::new();
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function featured(Builder $query): Builder
    {
        $query->flagged('featured');

        return $query;
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }

    protected function casts(): array
    {
        return [
            'level' => AnnouncementLevel::class,
            'scope' => AnnouncementScope::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
