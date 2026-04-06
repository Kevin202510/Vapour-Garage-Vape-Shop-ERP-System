@props(['overline' => null, 'title', 'subtitle' => null])

<div style="margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid rgba(124,34,212,0.2);display:flex;align-items:flex-end;justify-content:space-between;gap:16px">
    <div style="min-width:0;flex:1">
        @if($overline)
        <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.22em;text-transform:uppercase;color:rgba(124,34,212,0.6);margin-bottom:8px;display:flex;align-items:center;gap:8px">
            <span style="color:rgba(57,255,20,0.4)">//</span>{{ $overline }}
        </div>
        @endif

        <h1 style="font-family:'Russo One',sans-serif;font-size:26px;letter-spacing:0.04em;line-height:1.1;color:#e8e4f0;text-transform:uppercase">
            {!! $title !!}
        </h1>

        @if($subtitle)
        <p style="font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.08em;color:#7a7490;margin-top:6px">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions))
    <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        {{ $actions }}
    </div>
    @endif
</div>
