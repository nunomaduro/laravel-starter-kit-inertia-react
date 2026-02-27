<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class ShareablePolicy
{
    public function shareItem(User $user, Model $shareable): bool
    {
        if (! method_exists($shareable, 'canBeEditedBy')) {
            return false;
        }

        return $shareable->canBeEditedBy($user);
    }
}
