@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'inline-flex items-center px-1 pt-1 text-sm font-medium text-cotel-orange border-b-2 border-cotel-orange'
        : 'inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-transparent hover:text-cotel-orange hover:border-cotel-orange';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
