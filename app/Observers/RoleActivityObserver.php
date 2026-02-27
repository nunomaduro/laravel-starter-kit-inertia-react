<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

final class RoleActivityObserver
{
    public function created(Role $role): void
    {
        $this->log($role, ActivityType::RoleCreated, ['name' => $role->name, 'guard_name' => $role->guard_name]);
    }

    public function updated(Role $role): void
    {
        if (! $role->wasChanged()) {
            return;
        }
        $this->log($role, ActivityType::RoleUpdated, [
            'old' => array_intersect_key($role->getOriginal(), array_flip(['name', 'guard_name'])),
            'attributes' => $role->only(['name', 'guard_name']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function log(Role $role, ActivityType $type, array $properties): void
    {
        $logger = activity()->performedOn($role)->withProperties($properties);
        $causer = Auth::user();
        if ($causer instanceof Model) {
            $logger->causedBy($causer);
        }
        $logger->log($type->value);
    }
}
