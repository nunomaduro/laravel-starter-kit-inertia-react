<div
    @if (filament()->isSidebarCollapsibleOnDesktop())
        x-show="$store.sidebar.isOpen"
    @endif
    class="mx-2 mb-2"
>
    <a
        href="{{ url('/dashboard') }}"
        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-500 transition duration-75 hover:bg-gray-500/10 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200"
    >
        <x-filament::icon icon="heroicon-o-arrow-left" class="size-4 shrink-0" />
        <span>Back to app</span>
    </a>
</div>
