<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChangelogType;
use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\ChangelogEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Mattiverse\Userstamps\Traits\Userstamps;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Tags\HasTags;

/**
 * @property int $id
 * @property int|null $organization_id
 * @property string $title
 * @property string $description
 * @property string|null $version
 * @property ChangelogType $type
 * @property bool $is_published
 * @property \Carbon\CarbonImmutable|null $released_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
final class ChangelogEntry extends Model
{
    /** @use HasFactory<ChangelogEntryFactory> */
    use BelongsToOrganization;

    use HasFactory;
    use HasTags;
    use LogsActivity;
    use Searchable;
    use SoftDeletes;
    use Userstamps;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'version',
        'type',
        'is_published',
        'released_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'version', 'type', 'is_published', 'released_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('changelog_entry');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'version' => $this->version ?? '',
            'created_at' => $this->created_at?->timestamp ?? 0,
            'updated_at' => $this->updated_at?->timestamp ?? 0,
        ];
    }

    protected function casts(): array
    {
        return [
            'type' => ChangelogType::class,
            'is_published' => 'boolean',
            'released_at' => 'immutable_datetime',
        ];
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('released_at', '<=', now());
    }
}
