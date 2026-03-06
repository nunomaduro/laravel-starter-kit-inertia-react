@php
    use App\Services\TenantContext;

    if (! config('tenancy.enabled', true)) {
        return;
    }

    $user = filament()->auth()->user();
    if (! $user) {
        return;
    }

    $organizations = $user->isSuperAdmin()
        ? \App\Models\Organization::query()->orderBy('name')->get()
        : $user->organizations()->orderBy('name')->get();

    if ($organizations->isEmpty()) {
        return;
    }

    $current = TenantContext::get();
@endphp
@if ($organizations->isNotEmpty())
    <div
        @if (filament()->isSidebarCollapsibleOnDesktop())
            x-show="$store.sidebar.isOpen"
        @endif
        class="fi-sidebar-org-switcher mt-2"
    >
        <x-filament::dropdown
            placement="bottom-start"
            size="sm"
            class="fi-org-switcher"
        >
            <x-slot name="trigger">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-building-office-2"
                    icon-position="before"
                    class="fi-org-switcher-trigger w-full justify-start"
                    data-pan="admin-org-switcher"
                >
                    <span class="truncate">
                        {{ $current?->name ?? __('Select organization') }}
                    </span>
                </x-filament::button>
            </x-slot>

            <x-filament::dropdown.list>
                @foreach ($organizations as $org)
                    <form action="{{ route('organizations.switch') }}" method="POST" class="[&_.fi-dropdown-list-item]:w-full">
                        @csrf
                        <input type="hidden" name="organization_id" value="{{ $org->id }}" />
                        <button type="submit" class="fi-dropdown-list-item fi-org-switcher-item flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-sm outline-none transition duration-75 hover:bg-gray-500/10 dark:hover:bg-white/5">
                            @if ($current && $current->id === $org->id)
                                <x-filament::icon icon="heroicon-m-check" class="size-4 shrink-0" />
                            @else
                                <span class="size-4 shrink-0"></span>
                            @endif
                            <span class="truncate">{{ $org->name }}</span>
                        </button>
                    </form>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
@endif
