<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\VisibilityEnum;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that filters models based on visibility and sharing rules.
 *
 * - Global visibility: Visible to all
 * - Organization visibility: Only visible to members of the owning organization
 * - Shared visibility: Visible to owner org + explicitly shared targets
 */
final class VisibilityScope implements Scope
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if ($this->shouldBypassForSuperAdmin()) {
            return;
        }

        $userId = auth()->id();
        $orgId = TenantContext::id();

        if (! $userId || ! $orgId) {
            $builder->where($model->getTable().'.visibility', VisibilityEnum::Global->value);

            return;
        }

        $table = $model->getTable();
        $morphClass = $model->getMorphClass();

        $builder->where(function (Builder $query) use ($table, $morphClass, $userId, $orgId): void {
            $query->where($table.'.visibility', VisibilityEnum::Global->value)
                ->orWhere($table.'.organization_id', $orgId)
                ->orWhereExists(function (\Illuminate\Database\Query\Builder $sub) use ($table, $morphClass, $orgId): void {
                    $sub->selectRaw('1')
                        ->from('shareables')
                        ->whereColumn('shareables.shareable_id', $table.'.id')
                        ->where('shareables.shareable_type', $morphClass)
                        ->where('shareables.target_type', Organization::class)
                        ->where('shareables.target_id', $orgId)
                        ->where(function (\Illuminate\Database\Query\Builder $expiry): void {
                            $expiry->whereNull('shareables.expires_at')
                                ->orWhere('shareables.expires_at', '>', now());
                        });
                })
                ->orWhereExists(function (\Illuminate\Database\Query\Builder $sub) use ($table, $morphClass, $userId): void {
                    $sub->selectRaw('1')
                        ->from('shareables')
                        ->whereColumn('shareables.shareable_id', $table.'.id')
                        ->where('shareables.shareable_type', $morphClass)
                        ->where('shareables.target_type', User::class)
                        ->where('shareables.target_id', $userId)
                        ->where(function (\Illuminate\Database\Query\Builder $expiry): void {
                            $expiry->whereNull('shareables.expires_at')
                                ->orWhere('shareables.expires_at', '>', now());
                        });
                });
        });
    }

    private function shouldBypassForSuperAdmin(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        $key = config('tenancy.super_admin.view_all_session_key', 'view_all_organizations');

        return $user->isSuperAdmin() && session($key, false);
    }
}
