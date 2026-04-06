@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'ui-input-error']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
