@props(['type' => 'text', 'id', 'name', 'value' => '', 'required' => false, 'placeholder' => ''])

<input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
       {{ $required ? 'required' : '' }} placeholder="{{ $placeholder }}"
       {{ $attributes->merge(['class' => 'eb-input']) }}>
