@props(['value'])

<div class="relative inline-flex items-center justify-center">
    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
        <path class="text-gray-200" stroke-width="3.8" d="M18 2.0845
            a 15.9155 15.9155 0 0 1 0 31.831
            a 15.9155 15.9155 0 0 1 0 -31.831" />
        <path class="stroke-current text-primary" stroke-width="2.8" stroke-dasharray="{{ $value }}, 100" d="M18 2.0845
            a 15.9155 15.9155 0 0 1 0 31.831
            a 15.9155 15.9155 0 0 1 0 -31.831" />
    </svg>
    <span class="absolute text-sm font-semibold text-primary">{{ $value }}%</span>
</div>