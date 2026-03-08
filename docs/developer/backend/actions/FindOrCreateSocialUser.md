# FindOrCreateSocialUser

## Purpose

Finds an existing user by social provider account or creates a new user from OAuth provider data, then links the social account to the user.

## Location

`app/Actions/FindOrCreateSocialUser.php`

## Method Signature

```php
public function handle(string $provider, SocialiteUser $socialUser): User
```

## Dependencies

None (no constructor dependencies).

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$provider` | `string` | OAuth provider name (e.g., `'google'`, `'github'`) |
| `$socialUser` | `Laravel\Socialite\Contracts\User` | Socialite user object returned from OAuth callback |

## Return Value

Returns the authenticated `User` model — either an existing user (matched by social account or email) or a newly created user.

## Behaviour

1. Looks up an existing `SocialAccount` record by `provider` + `provider_id`.
2. If found, updates the stored OAuth token and returns the linked `User`.
3. If not found, looks up an existing `User` by email address.
4. If no user with that email exists, creates a new `User` with `email_verified_at` set and `password = null`.
5. Creates a `SocialAccount` record linking the provider to the user.
6. Fires `UserCreated` event when a brand-new user is created (triggers onboarding, gamification, etc.).

All database operations are wrapped in a `DB::transaction()`.

## Usage Examples

### From Controller

```php
$user = app(FindOrCreateSocialUser::class)->handle($provider, $socialUser);
```

### Via Dependency Injection

```php
public function callback(string $provider, FindOrCreateSocialUser $action): RedirectResponse
{
    $user = $action->handle($provider, $socialUser);
    auth()->login($user, remember: true);
}
```

## Related Components

- **Controller**: `SocialAuthController` (calls this action in `callback()`)
- **Model**: `User`, `SocialAccount`
- **Event**: `UserCreated` (fired for brand-new users)

## Notes

- New users created via OAuth have `password = null`. The `users.password` column was made nullable via migration `2026_03_08_000004_make_users_password_nullable`.
- Existing users who sign in with OAuth for the first time will have their existing account linked automatically via email match.
