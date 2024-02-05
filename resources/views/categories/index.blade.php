<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <a href="{{ route('categories.create') }}" class="px-10 link link-success text-uppercase">Create</a>
            <div class="p-6 text-gray-900">
                <div class="overflow-x-auto">
                    <table class="table bg-white">
                      <!-- head -->
                      <thead>
                        <tr>
                         
                          <th>Name</th>
                          <th>Department</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                    @foreach ($categories as $category)
                        
                  
                        <tr>
                          <th>{{ $category->name }}</th>
                          <td>{{ $category->department->name }}</td>
                          <td><a href="{{ route('categories.edit', $category) }}" >Edit</a></td>
                        
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
          
</x-app-layout>