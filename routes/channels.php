<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', fn ($user, $id): bool => (int) $user->id === (int) $id);

// Presence channel for the users DataTable — showcases presenceChannel prop.
// Returns user info so connected clients can show who else is viewing the table.
Broadcast::channel('presence-users', function ($user): array|bool {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar,
    ];
});
