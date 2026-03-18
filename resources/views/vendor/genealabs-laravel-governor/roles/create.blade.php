@extends (config("genealabs-laravel-governor.layout-view"))

@section ("content")
    <div>
        <div
            x-data="roleManagement()"
        >
            <x-governor-menu-bar />

            <div class="mt-6 mb-4 md:flex md:items-center md:justify-between">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold leading-7 text-gray-800 sm:text-3xl sm:truncate">
                        Create a new Role
                    </h1>
                </div>
            </div>


            <x-form
                :action="route('genealabs.laravel-governor.roles.store')"
                method="POST"
                class="flex flex-col"
            >
                @errors()

                <div class="hidden sm:block" aria-hidden="true">
                    <div class="py-5">
                        <div class="border-t border-gray-200"></div>
                    </div>
                </div>
                <div class="md:grid md:grid-cols-3 md:gap-6">
                    <div class="md:col-span-1">
                        <div class="px-4 sm:px-0">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Details</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Provide name and general description of the
                                purpose of this specific role, and what makes it
                                unique.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 md:mt-0 md:col-span-2">
                        <div class="shadow sm:rounded-md sm:overflow-hidden">
                            <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                                <div class="grid grid-cols-3 gap-6">
                                    <div class="col-span-3">
                                        <x-form-text
                                            name="name"
                                            class="form-input w-1/2 rounded-md border-gray-300"
                                            labelClasses="block text-sm font-medium text-gray-700"
                                        />
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-6">
                                    <div class="col-span-3">
                                        <x-form-textarea
                                            name="description"
                                            class="form-textarea w-full rounded-md border-gray-300"
                                            labelClasses="block text-sm font-medium text-gray-700"
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

                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">

                        @foreach ($permissionMatrix as $group => $permissions)
                            @if (count($permissionMatrix) > 1)
                                <h2
                                    class="mb-4 mt-8 text-lg font-bold leading-6 text-gray-800 sm:text-xl sm:truncate"
                                >
                                    {{ $group }}
                                </h2>
                            @endif

                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Entity
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Create
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                View Any
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                View
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Update
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Delete
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Restore
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Force Delete
                                            </th>
                                        </tr>
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 pt-0 pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                <x-form-select
                                                    name="createOwnerships"
                                                    label=""
                                                    class="form-select text-xs py-1 rounded-md border-gray-300"
                                                    :options="$ownerships->except(['own', 'other'])"
                                                    placeholder=" "
                                                    x-on:change="updateOwnerships('create', '{{ urlencode($group) }}', $event)"
                                                />
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 pt-0 pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                <x-form-select
                                                    name="viewAnyOwnerships"
                                                    label=""
                                                    class="form-select text-xs py-1 rounded-md border-gray-300"
                                                    :options="$ownerships"
                                                    placeholder=" "
                                                    x-on:change="updateOwnerships('viewAny', '{{ urlencode($group) }}', $event)"
                                                />
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 pt-0 pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                <x-form-select
                                                    name="viewOwnerships"
                                                    label=""
                                                    class="form-select text-xs py-1 rounded-md border-gray-300"
                                                    :options="$ownerships"
                                                    placeholder=" "
                                                    x-on:change="updateOwnerships('view', '{{ urlencode($group) }}', $event)"
                                                />
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 pt-0 pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                <x-form-select
                                                    name="updateOwnerships"
                                                    label=""
                                                    class="form-select text-xs py-1 rounded-md border-gray-300"
                                                    :options="$ownerships"
                                                    placeholder=" "
                                                    x-on:change="updateOwnerships('update', '{{ urlencode($group) }}', $event)"
                                                />
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 pt-0 pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                <x-form-select
                                                    name="createOwnerships"
                                                    label=""
                                                    class="form-select text-xs py-1 rounded-md border-gray-300"
                                                    :options="$ownerships"
                                                    placeholder=" "
                                                    x-on:change="updateOwnerships('delete', '{{ urlencode($group) }}', $event)"
                                                />
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 pt-0 pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                <x-form-select
                                                    name="restoreOwnerships"
                                                    label=""
                                                    class="form-select text-xs py-1 rounded-md border-gray-300"
                                                    :options="$ownerships"
                                                    placeholder=" "
                                                    x-on:change="updateOwnerships('restore', '{{ urlencode($group) }}', $event)"
                                                />
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-6 pt-0 pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                <x-form-select
                                                    name="forceDeleteOwnerships"
                                                    label=""
                                                    class="form-select text-xs py-1 rounded-md border-gray-300"
                                                    :options="$ownerships"
                                                    placeholder=" "
                                                    x-on:change="updateOwnerships('forceDelete', '{{ urlencode($group) }}', $event)"
                                                />
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($permissions as $entity => $permission)

                                            <tr
                                                class="{{ $loop->index % 2 === 1 ? "bg-gray-50" : "bg-white" }}"
                                            >
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                                >
                                                    {{ $entity }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <x-form-select
                                                        name="permissions[{{ urlencode($group) }}][{{ urlencode($entity) }}][create]"
                                                        label=""
                                                        class="form-select text-sm rounded-md border-gray-300"
                                                        :options="$ownerships"
                                                        :selectedValues="collect($permission['create'])"
                                                    />
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <x-form-select
                                                        name="permissions[{{ urlencode($group) }}][{{ urlencode($entity) }}][viewAny]"
                                                        label=""
                                                        class="form-select text-sm rounded-md border-gray-300"
                                                        :options="$ownerships"
                                                        :selectedValues="collect($permission['viewAny'])"
                                                    />
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                >
                                                    <x-form-select
                                                    name="permissions[{{ urlencode($group) }}][{{ urlencode($entity) }}][view]"
                                                    label=""
                                                        class="form-select text-sm rounded-md border-gray-300"
                                                        :options="$ownerships"
                                                        :selectedValues="collect($permission['view'])"
                                                    />
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                >
                                                    <x-form-select
                                                        name="permissions[{{ urlencode($group) }}][{{ urlencode($entity) }}][update]"
                                                        label=""
                                                        class="form-select text-sm rounded-md border-gray-300"
                                                        :options="$ownerships"
                                                        :selectedValues="collect($permission['update'])"
                                                    />
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                >
                                                    <x-form-select
                                                        name="permissions[{{ urlencode($group) }}][{{ urlencode($entity) }}][delete]"
                                                        label=""
                                                        class="form-select text-sm rounded-md border-gray-300"
                                                        :options="$ownerships"
                                                        :selectedValues="collect($permission['delete'])"
                                                    />
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                >
                                                    <x-form-select
                                                        name="permissions[{{ urlencode($group) }}][{{ urlencode($entity) }}][restore]"
                                                        label=""
                                                        class="form-select text-sm rounded-md border-gray-300"
                                                        :options="$ownerships"
                                                        :selectedValues="collect($permission['restore'])"
                                                    />
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                >
                                                    <x-form-select
                                                        name="permissions[{{ urlencode($group) }}][{{ urlencode($entity) }}][forceDelete]"
                                                        label=""
                                                        class="form-select text-sm rounded-md border-gray-300"
                                                        :options="$ownerships"
                                                        :selectedValues="collect($permission['forceDelete'])"
                                                    />
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        @endforeach

                        <x-form-submit
                            class="mt-4 py-1 px-4 bg-blue-600 text-blue-100 rounded-md hover:bg-blue-700"
                            value="Update Permissions"
                        />
                    </div>
                </div>
            </x-form>
        </div>
            <script>
            function roleManagement()
            {
                return {
                    entities: {!! json_encode($entities->pluck("name")) !!},

                    urlEncode: function (string) {
                        let newStr = '';
                        const len = string.length;

                        for (let i = 0; i < len; i++) {
                            let character = string.charAt(i);
                            let code = string.charCodeAt(i);

                            if (character === ' ') {
                                newStr += '+';
                            } else if (
                                (code < 48 && code !== 45 && code !== 46)
                                || (code < 65 && code > 57)
                                || (code > 90 && code < 97 && code !== 95)
                                || (code > 122)
                            ) {
                                newStr += '%' + code.toString(16);
                            } else {
                                newStr += character;
                            }
                        }

                        return newStr;
                    },

                    updateOwnerships: function (type, group, event) {
                        let ability = event.target.value;
                        let self = this;

                        this.entities.forEach(function (entity) {
                            document.querySelector("[name='permissions[" + group + "][" + self.urlEncode(entity) + "][" + type + "]']").value = ability;
                        });
                    },
                };
            }
        </script>
    </div>
@endsection
