@props(['active'])

@php
$classes = ($active?? false)? 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-semibold leading-5 transition duration-150 ease-in-out border-dbv-blue text-dbv-blue dark:text-blue-300 dark:border-blue-400'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 transition duration-150 ease-in-out text-gray-500 dark:text-gray-400 hover:text-dbv-blue dark:hover:text-blue-300 hover:border-blue-200 dark:hover:border-blue-700';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
