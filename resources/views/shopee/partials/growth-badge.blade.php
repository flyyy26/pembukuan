@php
    $small = $small ?? false;
    $sizeClass = $small ? 'text-[9px] px-1.5 py-0.5' : 'text-xs px-2 py-1';
@endphp

@if($growth === null)
    <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-500 {{ $sizeClass }} rounded-full font-medium">
        <iconify-icon icon="mdi:minus" class="text-[10px]"></iconify-icon>
        N/A
    </span>
@elseif($growth > 0)
    <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 {{ $sizeClass }} rounded-full font-medium">
        <iconify-icon icon="mdi:trending-up" class="text-[10px]"></iconify-icon>
        +{{ $growth }}%
    </span>
@elseif($growth < 0)
    <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 {{ $sizeClass }} rounded-full font-medium">
        <iconify-icon icon="mdi:trending-down" class="text-[10px]"></iconify-icon>
        {{ $growth }}%
    </span>
@else
    <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 {{ $sizeClass }} rounded-full font-medium">
        <iconify-icon icon="mdi:minus" class="text-[10px]"></iconify-icon>
        0%
    </span>
@endif