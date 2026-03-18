<?php

declare(strict_types=1);

namespace Modules\Announcements\Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Announcements\Enums\AnnouncementLevel;
use Modules\Announcements\Enums\AnnouncementScope;
use Modules\Announcements\Models\Announcement;

/**
 * Seeds rich announcement data for development (banners, DataTable, filters).
 */
final class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))->first();
        $createdBy = $admin?->id;
        $org = Organization::query()->where('name', 'Acme')->first();

        $announcements = [
            [
                'title' => 'Welcome to the platform',
                'body' => "We've launched new help articles and a contact form. Check out **Settings** for feature flags and preferences.",
                'level' => AnnouncementLevel::Info,
                'scope' => AnnouncementScope::Global,
                'organization_id' => null,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(14),
                'is_active' => true,
                'position' => 1,
                'created_by' => $createdBy,
            ],
            [
                'title' => 'Scheduled maintenance',
                'body' => 'Planned maintenance window: **Sunday 02:00–04:00 UTC**. Some features may be briefly unavailable.',
                'level' => AnnouncementLevel::Maintenance,
                'scope' => AnnouncementScope::Global,
                'organization_id' => null,
                'starts_at' => now(),
                'ends_at' => now()->addDays(7),
                'is_active' => true,
                'position' => 2,
                'created_by' => $createdBy,
            ],
        ];

        foreach ($announcements as $i => $data) {
            Announcement::query()->firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['position' => $i + 1]),
            );
        }

        if ($org && $createdBy) {
            Announcement::query()->firstOrCreate(
                ['title' => 'Acme team update'],
                [
                    'body' => 'Reminder: please complete your profile and review the new billing options in **Settings → Billing**.',
                    'level' => AnnouncementLevel::Warning,
                    'scope' => AnnouncementScope::Organization,
                    'organization_id' => $org->id,
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addDays(30),
                    'is_active' => true,
                    'position' => 1,
                    'created_by' => $createdBy,
                ],
            );
        }
    }
}
