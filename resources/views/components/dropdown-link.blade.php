@props(['active' => false])

@php
$classes = 'block w-full text-left px-4 py-2 text-sm
            text-gray-900 hover:text-cotel-orange hover:bg-orange-50';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
