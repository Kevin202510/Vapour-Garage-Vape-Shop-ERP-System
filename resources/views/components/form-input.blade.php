@props(['label', 'name', 'type' => 'text', 'required' => false, 'help' => null])

<div {{ $attributes->only('class') }}>
    @if($label)
    <label for="{{ $name }}" class="erp-label">
        {{ $label }}@if($required)<span class="erp-required">*</span>@endif
    </label>
    @endif
    <input
        id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        @if($required) required @endif
        {{ $attributes->except('class') }}
        class="erp-input {{ $errors->has($name) ? 'erp-input--error' : '' }}"
    >
    @error($name)
        <p class="erp-field-error">{{ $message }}</p>
    @enderror
    @if($help)
        <p class="erp-field-help">{{ $help }}</p>
    @endif
</div>
