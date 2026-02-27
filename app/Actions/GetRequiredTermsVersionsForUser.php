<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetRequiredTermsVersionsForUser
{
    /**
     * @return Collection<int, TermsVersion>
     */
    public function handle(User $user): Collection
    {
        $acceptedVersionIds = $user->termsAcceptances()->pluck('terms_version_id');

        return TermsVersion::query()
            ->where('is_required', true)
            ->whereNotIn('id', $acceptedVersionIds)
            ->oldest('effective_at')
            ->get();
    }
}
