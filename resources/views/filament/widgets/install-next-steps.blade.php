<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Next steps after install</x-slot>
        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
            <li>If you used <strong>Express install</strong>: change the default admin password (<code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">superadmin@example.com</code>) in your profile or User Management.</li>
            <li>Run <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">php artisan env:validate</code> to verify environment variables.</li>
            <li>Run <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">php artisan app:health</code> to check subsystems (database, cache, queue, mail).</li>
            <li>Configure Mail, Billing, and AI in <strong>Settings</strong> (Platform group) if you skipped them during install.</li>
            <li>Adjust feature flags in <strong>Settings → Feature Flag Settings</strong> if needed.</li>
            <li>Re-run the <strong>Setup Wizard</strong> (Platform → Setup Wizard) anytime to change app, tenancy, mail, billing, or AI defaults.</li>
            <li>Optional: set <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">HASHID_SALT</code>, <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">MAILS_LOGGING_ENABLED</code>, or <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">GOVERNOR_SUPERADMINS</code> in <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">.env</code> if using hash IDs, mail tracking, or Governor (see <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">.env.example</code>).</li>
        </ul>
        <div class="mt-3">
            <x-filament::button color="gray" size="sm" wire:click="dismiss">Dismiss</x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
