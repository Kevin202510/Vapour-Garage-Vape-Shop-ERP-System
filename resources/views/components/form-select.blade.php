@props(['label', 'name', 'required' => false, 'placeholder' => 'Select…'])

<div {{ $attributes->only('class') }}>
    @if($label)
    <label for="{{ $name }}" class="erp-label">
        {{ $label }}@if($required)<span class="erp-required">*</span>@endif
    </label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" @if($required) required @endif
            {{ $attributes->except('class') }}
            class="erp-select">
        <option value="">{{ $placeholder }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <p class="erp-field-error">{{ $message }}</p>
    @enderror
</div>
