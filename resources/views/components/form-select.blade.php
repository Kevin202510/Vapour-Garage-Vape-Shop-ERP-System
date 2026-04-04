@props(['label', 'name', 'required' => false, 'placeholder' => 'Select…'])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">
        {{ $label }}@if($required)<span class="text-terra ml-0.5">*</span>@endif
    </label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->except('class') }}
        class="w-full bg-cream border border-cream3 rounded-md px-3 py-2 text-[13px] font-sans text-ink
               outline-none focus:border-terra focus:ring-1 focus:ring-terra/20
               transition-colors @error($name) border-danger @enderror"
    >
        <option value="">{{ $placeholder }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <p class="mt-1 font-mono text-[10px] text-danger">{{ $message }}</p>
    @enderror
</div>
