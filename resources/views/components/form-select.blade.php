@props(['label', 'name', 'required' => false, 'placeholder' => 'Select…'])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" style="display:block;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(124,34,212,0.7);margin-bottom:7px">
        {{ $label }}@if($required)<span style="color:#39ff14;margin-left:3px">*</span>@endif
    </label>
    <select id="{{ $name }}" name="{{ $name }}" @if($required) required @endif
            {{ $attributes->except('class') }}
            style="width:100%;background:#0d0b14;border:1px solid rgba(124,34,212,0.3);border-radius:4px;padding:9px 14px;font-family:'Exo 2',sans-serif;font-size:13px;color:#e8e4f0;outline:none;transition:border-color 0.2s;cursor:pointer"
            onfocus="this.style.borderColor='rgba(124,34,212,0.7)'" onblur="this.style.borderColor='rgba(124,34,212,0.3)'">
        <option value="" style="background:#0d0b14">{{ $placeholder }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <p style="margin-top:5px;font-family:'Share Tech Mono',monospace;font-size:10px;color:#e05050">{{ $message }}</p>
    @enderror
</div>
