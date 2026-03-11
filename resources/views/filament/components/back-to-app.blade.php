<div
    @if (filament()->isSidebarCollapsibleOnDesktop())
        x-show="$store.sidebar.isOpen"
    @endif
    class="mb-1"
>
    <a
        href="{{ url('/dashboard') }}"
        class="group flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-sm font-medium text-gray-600 transition-all duration-150 dark:text-gray-400"
        style="background: white; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.06), 0 0 0 1px rgb(229 231 235);"
        x-on:mouseenter="$el.style.boxShadow='0 1px 3px 0 rgb(0 0 0 / 0.08), 0 0 0 1px rgb(209 213 219)';"
        x-on:mouseleave="$el.style.boxShadow='0 1px 2px 0 rgb(0 0 0 / 0.06), 0 0 0 1px rgb(229 231 235)';"
    >
        <span
            class="flex size-6 shrink-0 items-center justify-center rounded-md transition-all duration-150"
            style="background:rgb(249 250 251);box-shadow:0 0 0 1px rgb(229 231 235);"
            x-on:mouseenter="$el.style.background='rgb(var(--primary-600))';$el.style.boxShadow='0 0 0 1px rgb(var(--primary-600))';$el.querySelector('svg').style.color='white';"
            x-on:mouseleave="$el.style.background='rgb(249 250 251)';$el.style.boxShadow='0 0 0 1px rgb(229 231 235)';$el.querySelector('svg').style.color='';"
        >
            <x-filament::icon
                icon="heroicon-o-arrow-left"
                class="size-3.5 text-gray-400 transition-colors duration-150"
            />
        </span>
        <span>Back to app</span>
    </a>
</div>
