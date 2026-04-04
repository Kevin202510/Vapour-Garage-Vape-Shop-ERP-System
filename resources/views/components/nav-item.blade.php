@props(['route', 'icon', 'label', 'badge' => 0])

@php
    $baseRoute = str_ends_with($route, '.index')
        ? str_replace('.index', '', $route)
        : $route;

    $active = request()->routeIs($baseRoute . '.*') || request()->routeIs($route);
@endphp

<a href="{{ route($route) }}"
   class="relative flex items-center gap-2.5 px-6 py-[9px] text-[13px] transition-all
          {{ $active
              ? 'text-white bg-white/[0.07]'
              : 'text-white/50 hover:text-white/85 hover:bg-white/[0.04]' }}">

    @if($active)
        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-[60%] bg-terra-light rounded-r-sm"></span>
    @endif

    <x-icon :name="$icon" class="w-[15px] h-[15px] flex-shrink-0 {{ $active ? 'opacity-100' : 'opacity-70' }}" />

    <span>{{ $label }}</span>

    @if($badge > 0)
        <span class="ml-auto min-w-[18px] h-[18px] bg-terra text-white font-mono text-[9px] rounded-full flex items-center justify-center px-1.5">
            {{ $badge }}
        </span>
    @endif
</a>