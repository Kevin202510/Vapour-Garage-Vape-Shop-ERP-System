@props(['title' => null, 'subtitle' => null, 'class' => ''])

<div {{ $attributes->merge(['class' => '']) }}
     style="background:#130f1e;border:1px solid rgba(124,34,212,0.22);border-radius:6px;position:relative;overflow:hidden">

    {{-- Top accent line --}}
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#5b16a0,rgba(57,255,20,0.6),#5b16a0)"></div>

    {{-- Corner brackets --}}
    <div style="position:absolute;top:8px;left:8px;width:16px;height:16px;border-top:1.5px solid rgba(57,255,20,0.25);border-left:1.5px solid rgba(57,255,20,0.25)"></div>
    <div style="position:absolute;bottom:8px;right:8px;width:16px;height:16px;border-bottom:1.5px solid rgba(124,34,212,0.3);border-right:1.5px solid rgba(124,34,212,0.3)"></div>

    @if($title)
    <div style="padding:16px 20px 0;display:flex;align-items:baseline;gap:10px">
        <h3 style="font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.08em;color:#e8e4f0;text-transform:uppercase">{{ $title }}</h3>
        @if($subtitle)
        <span style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(124,34,212,0.6)">// {{ $subtitle }}</span>
        @endif
    </div>
    @endif

    <div style="padding:{{ $title ? '14px 20px 20px' : '20px' }}">
        {{ $slot }}
    </div>
</div>
