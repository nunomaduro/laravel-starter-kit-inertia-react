<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($this->shouldBypassScope()) {
            return;
        }

        $organizationId = TenantContext::id();

        if ($organizationId === null) {
            $builder->whereNull($model->getTable().'.organization_id');

            return;
        }

        $builder->where($model->getTable().'.organization_id', $organizationId);
    }

    private function shouldBypassScope(): bool
    {
        $key = config('tenancy.super_admin.view_all_session_key', 'view_all_organizations');
        if (! session($key, false)) {
            return false;
        }

        $user = auth()->user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
