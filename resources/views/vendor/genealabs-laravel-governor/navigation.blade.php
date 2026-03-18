@if (auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.role"))
    || auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.group"))
    || auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.team"))
    || auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.assignment"))
)
    <h3 class="flex items-center font-normal text-white mb-4 text-base no-underline">
        <svg class="sidebar-icon" aria-hidden="true" focusable="false" data-prefix="far" data-icon="key" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="var(--sidebar-icon)" d="M320 48c79.529 0 144 64.471 144 144s-64.471 144-144 144c-18.968 0-37.076-3.675-53.66-10.339L224 368h-32v48h-48v48H48v-96l134.177-134.177A143.96 143.96 0 0 1 176 192c0-79.529 64.471-144 144-144m0-48C213.965 0 128 85.954 128 192c0 8.832.602 17.623 1.799 26.318L7.029 341.088A24.005 24.005 0 0 0 0 358.059V488c0 13.255 10.745 24 24 24h144c13.255 0 24-10.745 24-24v-24h24c13.255 0 24-10.745 24-24v-20l40.049-40.167C293.106 382.604 306.461 384 320 384c106.035 0 192-85.954 192-192C512 85.965 426.046 0 320 0zm0 144c0 26.51 21.49 48 48 48s48-21.49 48-48-21.49-48-48-48-48 21.49-48 48z"></path></svg>
        <span class="sidebar-label">{{ __("Permissions") }}</span>
    </h3>
    <ul class="list-reset mb-8">

        @if (auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.role")))
            <li class="leading-tight mb-4 ml-8 text-sm">
                <router-link
                    :to="{name: 'laravel-nova-governor-roles'}"
                    class="text-white text-justify no-underline dim">
                    Roles
                </router-link>
            </li>
        @endif

        @if (auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.group")))
            <li class="leading-tight mb-4 ml-8 text-sm">
                <router-link
                    :to="{name: 'laravel-nova-governor-groups'}"
                    class="text-white text-justify no-underline dim">
                    Groups
                </router-link>
            </li>
        @endif

        @if (auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.team")))
            <li class="leading-tight mb-4 ml-8 text-sm">
                <router-link
                    :to="{path: '/resources/governor-teams'}"
                    class="text-white text-justify no-underline dim">
                    Teams
                </router-link>
            </li>
        @endif

        @if (auth()->user()->can("viewAny", config("genealabs-laravel-governor.models.assignment")))
            <li class="leading-tight mb-4 ml-8 text-sm">
                <router-link
                    :to="{name: 'laravel-nova-governor-assignments'}"
                    class="text-white text-justify no-underline dim">
                    Assignments
                </router-link>
            </li>
        @endif

    </ul>
@endif
