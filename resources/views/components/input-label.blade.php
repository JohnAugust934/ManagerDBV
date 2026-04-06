@props(['value'])

<label {{ $attributes->merge(['class' => 'ui-input-label']) }}>
    {{ $value?? $slot }}
</label>
