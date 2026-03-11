<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Next steps after install</x-slot>
        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
            <li>Run <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">php artisan env:validate</code> to verify environment variables.</li>
            <li>Run <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 py-0.5">php artisan app:health</code> to check subsystems (database, cache, queue, mail).</li>
            <li>Configure Mail, Billing, and AI in <strong>Settings</strong> (Platform group) if you skipped them during install.</li>
            <li>Adjust feature flags in <strong>Settings → Feature Flag Settings</strong> if needed.</li>
            <li>Re-run the <strong>Setup Wizard</strong> (Platform → Setup Wizard) anytime to change app, tenancy, mail, billing, or AI defaults.</li>
        </ul>
        <div class="mt-3">
            <x-filament::button color="gray" size="sm" wire:click="dismiss">Dismiss</x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
