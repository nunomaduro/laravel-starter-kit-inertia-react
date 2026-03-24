<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateNotificationPreferencesRequest;
use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationPreferencesController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        /** @var array<string, array{label: string, channels: list<string>}> $types */
        $types = config('notification-types', []);

        $existingPrefs = $user
            ->notificationPreferences()
            ->get()
            ->keyBy('notification_type')
            ->map(fn ($pref): array => [
                'via_database' => $pref->via_database,
                'via_email' => $pref->via_email,
            ])
            ->all();

        $preferences = collect($types)->map(function (array $config, string $key) use ($existingPrefs): array {
            $pref = $existingPrefs[$key] ?? ['via_database' => true, 'via_email' => true];

            return [
                'key' => $key,
                'label' => $config['label'],
                'channels' => $config['channels'],
                'via_database' => (bool) $pref['via_database'],
                'via_email' => (bool) $pref['via_email'],
            ];
        })->values()->all();

        return Inertia::render('settings/notifications', [
            'preferences' => $preferences,
        ]);
    }

    public function update(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        foreach ($validated['preferences'] as $pref) {
            NotificationPreference::query()->updateOrCreate(
                ['user_id' => $user->id, 'notification_type' => $pref['key']],
                ['via_database' => $pref['via_database'], 'via_email' => $pref['via_email']],
            );
        }

        return back()->with('success', 'Notification preferences saved.');
    }
}
