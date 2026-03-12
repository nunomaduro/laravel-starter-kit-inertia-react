<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Allowed columns for batch edit on the users table (logical/safe columns only). */
final readonly class BatchUpdateUsersAction
{
    public const array ALLOWED_COLUMNS = ['name', 'onboarding_completed'];

    /**
     * Update a single column for multiple users. Only allowed columns are applied.
     *
     * @param  array<int>  $ids
     * @return int Number of users updated
     */
    public function handle(array $ids, string $column, mixed $value): int
    {
        if (! in_array($column, self::ALLOWED_COLUMNS, true)) {
            return 0;
        }

        $count = DB::transaction(function () use ($ids, $column, $value): int {
            $cast = match ($column) {
                'onboarding_completed' => fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN),
                default => fn ($v) => is_string($v) ? $v : (string) $v,
            };

            return User::query()
                ->whereIn('id', $ids)
                ->update([$column => $cast($value)]);
        });

        return $count;
    }
}
