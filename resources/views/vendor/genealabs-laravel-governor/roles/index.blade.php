@extends (config("genealabs-laravel-governor.layout-view"))

@section ("content")
    <div>
        <x-governor-menu-bar />

        <div class="mt-6 mb-4 md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold leading-7 text-gray-800 sm:text-3xl sm:truncate">
                    Roles
                </h1>
            </div>
            <div class="mt-4 lex-shrink-0 flex md:mt-0 md:ml-4">
                <a
                    href="{{ route("genealabs.laravel-governor.roles.create") }}"
                    class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-500 hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500"
                >
                    Create Role
                </a>
            </div>
        </div>

        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Role
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">

                                @foreach ($roles as $role)
                                    <tr>
                                        <td>
                                            <a
                                                class="px-6 py-4 block whitespace-nowrap"
                                                href="{{ route("genealabs.laravel-governor.roles.edit", $role) }}"
                                            >
                                                <span
                                                    class="block text-sm font-medium text-gray-900"
                                                >
                                                    {{ $role->name }}
                                                </span>
                                                <span
                                                    class="block text-sm text-gray-500"
                                                >
                                                    {{ $role->description }}
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
