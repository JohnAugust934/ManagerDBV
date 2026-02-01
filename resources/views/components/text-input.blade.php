@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' =>
        'border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-dbv-blue dark:focus:border-blue-500 focus:ring-dbv-blue dark:focus:ring-blue-500 rounded-lg shadow-sm w-full py-2.5 transition-colors duration-200 placeholder-gray-400 dark:placeholder-gray-500',
]) !!}>
