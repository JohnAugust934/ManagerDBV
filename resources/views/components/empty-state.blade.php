@props([
    'title' => 'Nenhum registro encontrado',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'ui-empty']) }}>
    @if (isset($icon))
        <div class="mx-auto w-14 h-14 rounded-2xl bg-white/80 dark:bg-slate-800/80 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500">
            {{ $icon }}
        </div>
    @endif

    <h3 class="ui-empty-title">{{ $title }}</h3>

    @if ($description)
        <p class="ui-empty-description">{{ $description }}</p>
    @endif

    @if (isset($action))
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
