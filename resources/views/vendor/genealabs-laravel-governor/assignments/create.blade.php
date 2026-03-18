@extends (config("genealabs-laravel-governor.layout-view"))

@section ("content")
    <div>
        <x-governor-menu-bar />

        <div class="mt-6 mb-4 md:flex md:items-center md:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold leading-7 text-gray-800 sm:text-3xl sm:truncate">
                    Assignments
                </h1>
            </div>
        </div>


        <x-form
            :action="route('genealabs.laravel-governor.assignments.store')"
            method="POST"
            class="flex flex-col"
        >
            @errors()

            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Assignees
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white divide-y divide-gray-200"
                            >

                                @foreach ($roles as $role)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                        >
                                            {{ $role->name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                        >
                                            <x-form-select
                                                name="users[{{ $role->name }}][]"
                                                label=""
                                                class="w-full form-select text-xs py-1 rounded-md border-gray-300"
                                                :options="$users->pluck('id', 'name')"
                                                :selectedValues="$role->users->pluck('id')"
                                                multiple
                                            />
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                    <x-form-submit
                        class="mt-4 py-1 px-4 bg-blue-600 text-blue-100 rounded-md hover:bg-blue-700"
                        value="Update Assignments"
                    />
                </div>
            </div>
        </x-form>
    </div>
@endsection
