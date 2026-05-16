@props(['route', 'icon', 'label', 'badge' => 0])
@php $active = request()->routeIs($route) || request()->routeIs($route . '.*'); @endphp

<a href="{{ route($route) }}" class="vg-nav-item {{ $active ? 'vg-nav-item--active' : '' }}">
    <x-icon :name="$icon" class="vg-nav-item-icon" />
    <span>{{ $label }}</span>
    @if($badge > 0)
        <span class="vg-nav-badge">{{ $badge }}</span>
    @endif
</a>
