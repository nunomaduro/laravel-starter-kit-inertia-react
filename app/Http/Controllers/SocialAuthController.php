<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FindOrCreateSocialUser;
use App\Settings\AuthSettings;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

final class SocialAuthController extends Controller
{
    private const array SUPPORTED_PROVIDERS = ['google', 'github'];

    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, FindOrCreateSocialUser $action): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return to_route('login')->withErrors(['social' => 'Authentication failed. Please try again.']);
        }

        $user = $action->handle($provider, $socialUser);

        auth()->login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function validateProvider(string $provider): void
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        $settings = resolve(AuthSettings::class);

        $enabled = match ($provider) {
            'google' => $settings->google_oauth_enabled,
            'github' => $settings->github_oauth_enabled,
            default => false,
        };

        abort_unless($enabled, 404, sprintf("OAuth provider '%s' is not enabled.", $provider));
    }
}
