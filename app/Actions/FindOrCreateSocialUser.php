<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\User\UserCreated;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

final readonly class FindOrCreateSocialUser
{
    public function handle(string $provider, SocialiteUser $socialUser): User
    {
        return DB::transaction(function () use ($provider, $socialUser): User {
            $socialAccount = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                $socialAccount->update([
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'token_expires_at' => $socialUser->expiresIn
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]);

                return $socialAccount->user;
            }

            $user = User::query()->where('email', $socialUser->getEmail())->first();

            $isNew = false;
            if (! $user) {
                $user = User::query()->create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    'email_verified_at' => now(),
                    'password' => null,
                ]);
                $isNew = true;
            }

            SocialAccount::query()->create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'token_expires_at' => $socialUser->expiresIn
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
            ]);

            if ($isNew) {
                event(new UserCreated($user));
            }

            return $user;
        });
    }
}
