@props(['title' => null])

<div style="background:#130f1e;border:1px solid rgba(124,34,212,0.22);border-radius:6px;overflow:hidden;position:relative">
    {{-- Top accent --}}
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#5b16a0,rgba(57,255,20,0.5),#5b16a0)"></div>

    @if($title || isset($tableHeader))
    <div style="padding:14px 20px;border-bottom:1px solid rgba(124,34,212,0.18);display:flex;align-items:center;justify-content:space-between;background:rgba(0,0,0,0.2)">
        @if($title)
        <h3 style="font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.1em;color:#e8e4f0;text-transform:uppercase">{{ $title }}</h3>
        @endif
        @if(isset($tableHeader))
        <div style="display:flex;align-items:center;gap:10px">{{ $tableHeader }}</div>
        @endif
    </div>
    @endif

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            @if(isset($head))
            <thead>
                <tr style="border-bottom:1px solid rgba(124,34,212,0.18)">{{ $head }}</tr>
            </thead>
            @endif
            <tbody>{{ $slot }}</tbody>
        </table>
    </div>

    @if(isset($pagination))
    <div style="padding:12px 20px;border-top:1px solid rgba(124,34,212,0.18);background:rgba(0,0,0,0.2)">
        {{ $pagination }}
    </div>
    @endif
</div>
