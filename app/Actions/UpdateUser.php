<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

final readonly class UpdateUser
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes, ?Request $request = null): void
    {
        $emailChanged = array_key_exists('email', $attributes) && $user->email !== $attributes['email'];

        $user->update([
            ...Arr::except($attributes, 'avatar'),
            ...($emailChanged ? ['email_verified_at' => null] : []),
        ]);

        if ($request?->hasFile('avatar')) {
            $user->clearMediaCollection('avatar');
            $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatar');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
