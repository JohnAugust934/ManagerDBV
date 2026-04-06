@props(['active'])

@php
$classes = ($active?? false)? 'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-semibold transition duration-150 ease-in-out border-dbv-blue bg-blue-50 text-dbv-blue dark:border-blue-400 dark:bg-blue-900/30 dark:text-blue-200'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium transition duration-150 ease-in-out text-gray-600 dark:text-gray-400 hover:text-dbv-blue dark:hover:text-blue-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-blue-200 dark:hover:border-blue-700';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
