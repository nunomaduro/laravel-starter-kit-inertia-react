@if (\Str::startsWith(request()->url(), $url))
    <a
        href="{{ $url }}"
        class="bg-white text-gray-800 px-3 py-2 font-medium text-sm rounded-md"
        aria-current="page"
    >
        {{ $label }}
    </a>
@else
    <a
        href="{{ $url }}"
        class="text-gray-600 hover:text-gray-800 px-3 py-2 font-medium text-sm rounded-md"
    >
        {{ $label }}
    </a>
@endif
