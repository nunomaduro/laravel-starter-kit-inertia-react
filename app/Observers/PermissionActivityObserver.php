<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

final class PermissionActivityObserver
{
    public function created(Permission $permission): void
    {
        $this->log($permission, ActivityType::PermissionCreated, ['name' => $permission->name, 'guard_name' => $permission->guard_name]);
    }

    public function updated(Permission $permission): void
    {
        if (! $permission->wasChanged()) {
            return;
        }
        $this->log($permission, ActivityType::PermissionUpdated, [
            'old' => array_intersect_key($permission->getOriginal(), array_flip(['name', 'guard_name'])),
            'attributes' => $permission->only(['name', 'guard_name']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function log(Permission $permission, ActivityType $type, array $properties): void
    {
        $logger = activity()->performedOn($permission)->withProperties($properties);
        $causer = Auth::user();
        if ($causer instanceof Model) {
            $logger->causedBy($causer);
        }
        $logger->log($type->value);
    }
}
