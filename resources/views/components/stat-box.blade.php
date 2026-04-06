@props(['label', 'value', 'valueClass' => ''])

<div style="background:#130f1e;border:1px solid rgba(124,34,212,0.2);border-radius:6px;padding:18px 20px;position:relative;overflow:hidden">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,rgba(124,34,212,0.6),transparent)"></div>
    <p style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:#7a7490;margin-bottom:8px">{{ $label }}</p>
    <p style="font-family:'Russo One',sans-serif;font-size:26px;line-height:1;color:{{ str_contains($valueClass,'ok') ? '#39ff14' : (str_contains($valueClass,'warn') ? '#c9943a' : '#e8e4f0') }}">{{ $value }}</p>
</div>
