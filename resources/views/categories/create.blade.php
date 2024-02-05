<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
    <x-card title="add categories" subtitle="" shadow separator>
        <form action="{{ route('categories.store') }}" method="POST" class="max-w-md">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-gray-600">Name:</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border rounded-md" value="{{ old('name') }}">
            </div>
        <div>
            <button type="submit" class="px-4 py-2 text-white bg-green-500 rounded-md">Create Category</button>
        </div>
        </form>
    </x-card>
                </div>
            </div>
        </div>
    </div>
    
          
</x-app-layout>