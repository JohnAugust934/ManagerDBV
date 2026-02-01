<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-3 bg-dbv-blue dark:bg-blue-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 dark:hover:bg-blue-600 focus:bg-blue-800 dark:focus:bg-blue-600 active:bg-blue-900 dark:active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-dbv-gold focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
