<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

       <style>
        .custom-modal-width .modal-content {
    max-width: 96rem; /* or any other width you prefer */
}
       </style>
  <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
  <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>


        {{-- Sortable.js --}}
        
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>


        
        @livewireStyles

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-rich-text-trix-styles />

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow dark:bg-gray-800">
                    <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <x-toast />  

            <main>
                {{ $slot }}
            </main>
        </div>
      

        @livewireScripts
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Livewire.on('openTaskDetailsModal', () => {
                    modal17.showModal(); // Use consistent modal name
                });
        
                Livewire.on('closeTaskDetailsModal', () => {
                    modal17.close(); // Use consistent modal name
                });
            });
        </script>
    </body>
</html>
