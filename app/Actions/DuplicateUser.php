<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class DuplicateUser
{
    /**
     * Duplicate a user (name + " (copy)", unique email, same orgs). Demo for DataTable replicate action.
     */
    public function handle(User $user): User
    {
        return DB::transaction(function () use ($user): User {
            $copy = User::query()->create([
                'name' => $user->name.' (copy)',
                'email' => 'copy-'.$user->id.'-'.Str::random(6).'@example.com',
                'password' => bcrypt(Str::random(32)),
                'onboarding_completed' => false,
            ]);

            $orgIds = $user->organizations()->pluck('organizations.id')->all();
            if ($orgIds !== []) {
                $copy->organizations()->attach($orgIds);
            }

            return $copy;
        });
    }
}
