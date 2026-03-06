<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class BulkSoftDeleteUsers
{
    /**
     * Soft-delete users by id. Skips the current user. Used for DataTable bulk action demo.
     *
     * @param  array<int>  $ids
     * @return int Number of users soft-deleted
     */
    public function handle(array $ids, ?User $currentUser): int
    {
        return DB::transaction(function () use ($ids, $currentUser): int {
            $query = User::query()->whereIn('id', $ids);

            if ($currentUser instanceof User) {
                $query->where('id', '!=', $currentUser->id);
            }

            $users = $query->get();
            $users->each->delete();

            return $users->count();
        });
    }
}
