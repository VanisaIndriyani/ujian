@props([
    'route',
    'icon' => '•',
    'params' => [],
])

@php
    $isActive = request()->routeIs($route);
@endphp

<a href="{{ route($route, $params) }}"
   class="flex items-center gap-3 px-4 py-2 rounded-lg transition
        {{ $isActive ? 'bg-emerald-500 text-white shadow' : 'text-emerald-100 hover:bg-emerald-600/60' }}">
    <span class="text-lg">{!! $icon !!}</span>
    <span class="text-sm font-medium">{{ $slot }}</span>
</a>

