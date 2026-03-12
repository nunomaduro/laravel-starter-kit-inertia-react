<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\User;
use App\Support\AssignRoleViaDb;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use SensitiveParameter;
use Spatie\Permission\Models\Role;

final readonly class CreateUser
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, #[SensitiveParameter] string $password): User
    {
        $user = User::query()->create([
            ...$attributes,
            'password' => $password,
        ]);

        $defaultRole = config('permission.default_role');
        if (is_string($defaultRole) && $defaultRole !== '' && Role::query()->where('name', $defaultRole)->exists()) {
            AssignRoleViaDb::assignGlobal($user, [$defaultRole]);
            $logger = activity()->performedOn($user)->withProperties(['attributes' => [$defaultRole]]);
            $causer = Auth::user();
            if ($causer instanceof Model) {
                $logger->causedBy($causer);
            }

            $logger->log(ActivityType::RolesAssigned->value);
        }

        event(new Registered($user));

        return $user;
    }
}
