<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use Illuminate\Database\Seeder;

final class NotificationSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $users = User::query()->limit(5)->get();

        if ($users->isEmpty()) {
            return;
        }

        $templates = [
            ['Welcome to the app', 'Your account is ready. Explore the dashboard and settings.', 'success', null],
            ['New feature: Billing', 'Invoice and subscription management is now available under Billing.', 'info', '/billing'],
            ['Reminder: Complete your profile', 'Add a profile photo and timezone in Settings → General.', 'warning', '/settings/general'],
            ['Scheduled maintenance', 'Planned maintenance Sunday 02:00–04:00 UTC. Brief downtime possible.', 'info', null],
            ['Payment received', 'Thank you for your payment. Your invoice is available in Billing.', 'success', '/billing/invoices'],
            ['Team update', 'A new member joined your organization. Review in Settings → Members.', 'info', '/settings/members'],
            ['Security tip', 'Enable two-factor authentication in Settings for extra security.', 'warning', '/settings/security'],
            ['Product update', 'Check out the latest changelog for new features and fixes.', 'info', '/changelog'],
        ];

        foreach ($users as $user) {
            $count = random_int(8, 12);
            $used = [];

            for ($i = 0; $i < $count; $i++) {
                $template = fake()->randomElement($templates);
                $key = implode('|', $template);
                if (isset($used[$key]) && $used[$key] >= 2) {
                    $template = fake()->randomElement($templates);
                }
                $used[implode('|', $template)] = ($used[implode('|', $template)] ?? 0) + 1;

                $user->notify(new GenericDatabaseNotification(
                    $template[0],
                    $template[1],
                    $template[2],
                    $template[3],
                ));
            }

            $user->unreadNotifications()->limit(3)->get()->each(function ($n): void {
                $n->markAsRead();
            });
        }
    }
}
