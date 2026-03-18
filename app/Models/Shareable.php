<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ShareableFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A record of a shareable item shared with a target (Organization or User).
 *
 * @property int $id
 * @property string $shareable_type
 * @property int $shareable_id
 * @property string $target_type
 * @property int $target_id
 * @property string $permission
 * @property int $shared_by
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $shareable
 * @property-read Model $target
 * @property-read User $sharer
 *
 * @method static Builder<static>|Shareable active()
 * @method static ShareableFactory factory($count = null, $state = [])
 * @method static Builder<static>|Shareable forTarget(Model $target)
 * @method static Builder<static>|Shareable withEditPermission()
 */
final class Shareable extends Model
{
    /** @use HasFactory<ShareableFactory> */
    use HasFactory;

    /**
     * Valid permission values for shares.
     *
     * @var list<string>
     */
    public const array VALID_PERMISSIONS = ['view', 'edit'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shareable_type',
        'shareable_id',
        'target_type',
        'target_id',
        'permission',
        'shared_by',
        'expires_at',
    ];

    public static function isValidPermission(string $permission): bool
    {
        return in_array($permission, self::VALID_PERMISSIONS, true);
    }

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sharer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    public function canEdit(): bool
    {
        return $this->permission === 'edit';
    }

    public function canView(): bool
    {
        return in_array($this->permission, ['view', 'edit'], true);
    }

    /**
     * @param  Builder<Shareable>  $query
     * @return Builder<Shareable>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * @param  Builder<Shareable>  $query
     * @return Builder<Shareable>
     */
    #[Scope]
    protected function forTarget(Builder $query, Model $target): Builder
    {
        return $query->where('target_type', $target->getMorphClass())
            ->where('target_id', $target->getKey());
    }

    /**
     * @param  Builder<Shareable>  $query
     * @return Builder<Shareable>
     */
    #[Scope]
    protected function withEditPermission(Builder $query): Builder
    {
        return $query->where('permission', 'edit');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
