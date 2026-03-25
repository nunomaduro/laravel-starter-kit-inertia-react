<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DataTableSavedView extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'table_name',
        'name',
        'filters',
        'sort',
        'columns',
        'column_order',
        'is_default',
        'organization_id',
        'is_shared',
        'is_system',
        'created_by',
    ];

    /**
     * Get views grouped into three tiers for a given table.
     *
     * Uses a single query and partitions the results in PHP.
     *
     * @return array{my_views: Collection<int, DataTableSavedView>, team_views: Collection<int, DataTableSavedView>, system_views: Collection<int, DataTableSavedView>}
     */
    public static function grouped(string $tableName, int $userId, ?int $orgId): array
    {
        $all = self::query()
            ->where('table_name', $tableName)
            ->where(function ($query) use ($userId, $orgId): void {
                // Private views for the current user
                $query->where(function ($q) use ($userId): void {
                    $q->where('user_id', $userId)
                        ->where('is_shared', false)
                        ->where('is_system', false);
                });

                // Org-scoped views (shared + system)
                if ($orgId !== null) {
                    $query->orWhere(function ($q) use ($orgId): void {
                        $q->where('organization_id', $orgId)
                            ->where(function ($inner): void {
                                $inner->where('is_shared', true)
                                    ->orWhere('is_system', true);
                            });
                    });
                }
            })
            ->orderBy('name')
            ->get();

        $myViews = $all->filter(fn (self $v): bool => $v->user_id === $userId && ! $v->is_shared && ! $v->is_system)->values();

        $teamViews = $all->filter(fn (self $v): bool => $v->is_shared && ! $v->is_system)->values();

        $systemViews = $all->filter(fn (self $v): bool => $v->is_system)->values();

        return [
            'my_views' => $myViews,
            'team_views' => $teamViews,
            'system_views' => $systemViews,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** Scope: private views owned by a specific user. */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
            ->where('is_shared', false)
            ->where('is_system', false);
    }

    /** Scope: shared (team) views within an organization. */
    public function scopeSharedInOrg(Builder $query, int $orgId): Builder
    {
        return $query->where('organization_id', $orgId)
            ->where('is_shared', true)
            ->where('is_system', false);
    }

    /** Scope: system-wide views within an organization. */
    public function scopeSystemInOrg(Builder $query, int $orgId): Builder
    {
        return $query->where('organization_id', $orgId)
            ->where('is_system', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'column_order' => 'array',
            'is_default' => 'boolean',
            'is_shared' => 'boolean',
            'is_system' => 'boolean',
        ];
    }
}
