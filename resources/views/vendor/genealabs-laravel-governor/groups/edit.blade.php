@extends (config("genealabs-laravel-governor.layout-view"))

@section ("content")
    <div>
        <x-governor-menu-bar />

        <div class="mt-6 mb-4 md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold leading-7 text-gray-800 sm:text-3xl sm:truncate">
                    Edit "{{ $group->name }}" Group
                </h1>
            </div>
            <div class="mt-4 lex-shrink-0 flex md:mt-0 md:ml-4">
                <x-form
                    :action="route('genealabs.laravel-governor.groups.destroy', $group)"
                    :model="$group"
                    method="DELETE"
                >
                    <x-form-submit
                        class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-red-500"
                        value="Delete"
                    />
                </x-form>
            </div>
        </div>



        <x-form
            :action="route('genealabs.laravel-governor.groups.store')"
            :model="$group"
            method="POST"
            class=""
        >
            <div class="hidden sm:block" aria-hidden="true">
                <div class="py-5">
                    <div class="border-t border-gray-200"></div>
                </div>
            </div>
            <div
                class="md:grid md:grid-cols-3 md:gap-6"
            >
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">General</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Name your group according to the purpose it will fulfill.
                        </p>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="grid grid-cols-3 gap-6">
                                <div class="col-span-6">
                                    <x-form-text
                                        class="w-1/2 rounded-md border-gray-300"
                                        name="name"
                                        labelClasses="block"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hidden sm:block" aria-hidden="true">
                <div class="py-5">
                    <div class="border-t border-gray-200"></div>
                </div>
            </div>
            <div class="mt-10 sm:mt-0">
                <div class="md:grid md:grid-cols-3 md:gap-6">
                    <div class="md:col-span-1">
                        <div class="px-4 sm:px-0">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Entities</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Select all entities that are part of this functional grouping.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 md:mt-0 md:col-span-2">
                        <div class="shadow overflow-hidden sm:rounded-md">
                            <div class="px-4 py-5 bg-white sm:p-6">
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6">
                                        <x-form-select
                                            class="w-full rounded-md border-gray-300"
                                            name="entity_names[]"
                                            :multiple="true"
                                            :options="$entities->pluck('name', 'name')"
                                            :selectedValues="$group->entities->pluck('name', 'name')"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <x-form-submit
                            class="mt-4 py-1 px-4 bg-blue-600 text-blue-100 rounded-md hover:bg-blue-700"
                            value="Update Permissions"
                        />
                    </div>
                </div>
            </div>
        </x-form>
    </div>
@endsection
