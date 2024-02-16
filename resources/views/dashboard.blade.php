<x-app-layout>
    <style>
        @keyframes flash {
        0% { opacity: 1; }
        50% { opacity: 0; }
        100% { opacity: 1; }
    }
    
    .flash-alert {
        animation: flash 1s infinite;
    }
    
    </style>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    
    <livewire:report.dashboard />

</x-app-layout>

