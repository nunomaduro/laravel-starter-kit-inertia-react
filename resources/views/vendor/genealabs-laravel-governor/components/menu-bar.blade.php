<div>
    <div class="sm:hidden">
        <label for="tabs" class="sr-only">Select a tab</label>
        <select
            id="tabs"
            name="tabs"
            class="block w-full focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md"
        >

            @foreach ($menuBarItems as $menuBarItem)
                <option>{{ $menuBarItem->label }}</option>
            @endforeach

        </select>
    </div>
    <div class="hidden sm:block">
        <nav class="flex space-x-4" aria-label="Tabs">

            @foreach ($menuBarItems as $menuBarItem)
                <x-genealabs-laravel-governor::menu-bar-item
                    label="{{ $menuBarItem->label }}"
                    url="{{ $menuBarItem->url }}"
                />
            @endforeach

        </nav>
    </div>
</div>
