@props(['title', 'description' => null, 'icon' => 'box'])

<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:56px 24px;text-align:center">
    <div style="width:52px;height:52px;border-radius:6px;background:rgba(124,34,212,0.12);border:1px solid rgba(124,34,212,0.25);display:flex;align-items:center;justify-content:center;margin-bottom:16px">
        <x-icon :name="$icon" style="width:22px;height:22px;color:rgba(124,34,212,0.6)" />
    </div>
    <p style="font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.06em;color:#c8c4d4;margin-bottom:6px;text-transform:uppercase">{{ $title }}</p>
    @if($description)
    <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#7a7490;max-width:280px;line-height:1.5">{{ $description }}</p>
    @endif
    @if(isset($action))
    <div style="margin-top:16px">{{ $action }}</div>
    @endif
</div>
