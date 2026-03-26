---
title: Single-Purpose Action Classes
impact: HIGH
impactDescription: Improves reusability and testability
tags: architecture, actions, single-responsibility, testability
---

## Single-Purpose Action Classes

**Impact: HIGH (Improves reusability and testability)**

Use single-purpose action classes for discrete operations to achieve maximum reusability and testability.

## Bad Example

```php
// Service class doing too many things
class UserService
{
    public function register(array $data): User
    {
        // Registration logic...
    }

    public function updateProfile(User $user, array $data): User
    {
        // Profile update logic...
    }

    public function deactivate(User $user): void
    {
        // Deactivation logic...
    }

    public function sendWelcomeEmail(User $user): void
    {
        // Email logic...
    }

    public function calculateStats(User $user): array
    {
        // Stats calculation...
    }
}
```

## Good Example

```php
// app/Actions/User/RegisterUserAction.php
namespace App\Actions\User;

use App\Models\User;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function __construct(
        private SendWelcomeEmailAction $sendWelcomeEmail,
    ) {}

    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new UserRegistered($user));
        $this->sendWelcomeEmail->execute($user);

        return $user;
    }
}
```

```php
// app/Actions/User/SendWelcomeEmailAction.php
namespace App\Actions\User;

use App\Models\User;
use App\Notifications\WelcomeNotification;

class SendWelcomeEmailAction
{
    public function execute(User $user): void
    {
        $user->notify(new WelcomeNotification());
    }
}
```

```php
// app/Actions/User/UpdateUserProfileAction.php
namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UpdateUserProfileAction
{
    public function execute(User $user, array $data): User
    {
        if (isset($data['avatar'])) {
            $data['avatar_path'] = $this->storeAvatar($data['avatar']);
            unset($data['avatar']);
        }

        $user->update($data);

        return $user->fresh();
    }

    private function storeAvatar($avatar): string
    {
        return Storage::disk('public')->put('avatars', $avatar);
    }
}
```

```php
// Controller using actions
namespace App\Http\Controllers;

use App\Actions\User\RegisterUserAction;
use App\Actions\User\UpdateUserProfileAction;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function store(
        RegisterUserRequest $request,
        RegisterUserAction $action
    ) {
        $user = $action->execute($request->validated());

        return new UserResource($user);
    }

    public function update(
        UpdateUserProfileRequest $request,
        UpdateUserProfileAction $action
    ) {
        $user = $action->execute(
            auth()->user(),
            $request->validated()
        );

        return new UserResource($user);
    }
}
```

## Why

- **Single responsibility**: Each action does exactly one thing
- **Highly testable**: Small, focused units are easy to test
- **Reusable**: Actions can be called from controllers, jobs, commands, or other actions
- **Self-documenting**: Action names clearly describe what they do
- **Easy to find**: Organized by domain in the Actions folder
- **Composable**: Actions can be combined to build complex workflows
