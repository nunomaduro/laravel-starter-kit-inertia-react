<div
    @if (filament()->isSidebarCollapsibleOnDesktop())
        x-show="$store.sidebar.isOpen"
    @endif
    class="mb-1"
>
    <a
        href="{{ url('/dashboard') }}"
        class="group flex w-full flex-nowrap items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:border-white/20 dark:hover:bg-white/10 dark:hover:text-white"
    >
        <x-filament::icon
            icon="heroicon-o-arrow-left"
            class="size-4 shrink-0 text-gray-500 transition-colors group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200"
        />
        <span class="whitespace-nowrap">Back to app</span>
    </a>
</div>
