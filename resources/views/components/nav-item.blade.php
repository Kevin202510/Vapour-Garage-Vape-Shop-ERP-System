@props(['route', 'icon', 'label', 'badge' => 0])
@php $active = request()->routeIs($route) || request()->routeIs($route . '.*'); @endphp

<a href="{{ route($route) }}"
   style="display:flex;align-items:center;gap:10px;padding:9px 20px;font-family:'Exo 2',sans-serif;font-size:13px;font-weight:{{ $active ? '600' : '400' }};text-decoration:none;transition:all 0.15s;position:relative;
          color:{{ $active ? '#e8e4f0' : '#7a7490' }};
          background:{{ $active ? 'rgba(124,34,212,0.15)' : 'transparent' }};"
   onmouseover="if(!{{ $active ? 'true' : 'false' }})this.style.color='#c8c4d4',this.style.background='rgba(124,34,212,0.07)'"
   onmouseout="if(!{{ $active ? 'true' : 'false' }})this.style.color='#7a7490',this.style.background='transparent'">

    {{-- Active left bar --}}
    @if($active)
    <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);width:2px;height:60%;background:linear-gradient(180deg,#7c22d4,#39ff14);border-radius:0 2px 2px 0"></span>
    @endif

    {{-- Icon --}}
    <span style="flex-shrink:0;opacity:{{ $active ? '1' : '0.6' }}">
        <x-icon :name="$icon" style="width:15px;height:15px;{{ $active ? 'color:#9b3df0' : 'color:#7a7490' }}" />
    </span>

    <span>{{ $label }}</span>

    @if($badge > 0)
    <span style="margin-left:auto;min-width:18px;height:18px;background:#7c22d4;color:#fff;font-family:'Share Tech Mono',monospace;font-size:9px;border-radius:2px;display:flex;align-items:center;justify-content:center;padding:0 4px;box-shadow:0 0 8px rgba(124,34,212,0.5)">
        {{ $badge }}
    </span>
    @endif
</a>
