<x-app-layout>

    <div class="w-full py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-2 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="flex p-2">
                    <a href="{{ route('admin.permissions.index') }}"
                        class="px-4 py-2 bg-green-700 rounded-md hover:bg-green-500 text-slate-100">Permission Index</a>
                </div>
                <div class="flex flex-col p-2 bg-slate-100">
                    <div class="w-1/2 mt-10 space-y-8 divide-y divide-gray-200">
                        <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
                            @csrf
                            @method('PUT')
                            <div class="sm:col-span-6">
                                <label for="name" class="block text-sm font-medium text-gray-700"> Permission name
                                </label>
                                <div class="mt-1">
                                    <input type="text" id="name" name="name"
                                        class="block w-full px-3 py-2 text-base leading-normal transition duration-150 ease-in-out bg-white border border-gray-400 rounded-md appearance-none sm:text-sm sm:leading-5"
                                        value="{{ $permission->name }}" />
                                </div>
                                @error('name')
                                    <span class="text-sm text-red-400">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="pt-5 sm:col-span-6">
                                <button type="submit"
                                    class="px-4 py-2 bg-green-500 rounded-md hover:bg-green-700">Update</button>
                            </div>
                        </form>
                    </div>
                    <div class="p-2 mt-6 bg-slate-100">
                        <h2 class="text-2xl font-semibold">Roles</h2>
                        <div class="flex p-2 mt-4 space-x-2">
                            @if ($permission->roles)
                                @foreach ($permission->roles as $permission_role)
                                    <form class="px-4 py-2 text-black bg-red-500 rounded-md hover:bg-red-700" method="POST"
                                        action="{{ route('admin.permissions.roles.remove', [$permission->id, $permission_role->id]) }}"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">{{ $permission_role->name }}</button>
                                    </form>
                                @endforeach
                            @endif
                        </div>
                        <div class="max-w-xl mt-6">
                            <form method="POST" action="{{ route('admin.permissions.roles', $permission->id) }}">
                                @csrf
                                <div class="sm:col-span-6">
                                    <label for="role" class="block text-sm font-medium text-gray-700">Roles</label>
                                    <select id="role" name="role" autocomplete="role-name"
                                        class="block w-full px-3 py-2 mt-1 bg-black border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('role')
                                    <span class="text-sm text-red-400">{{ $message }}</span>
                                @enderror
                        </div>
                        <div class="pt-5 sm:col-span-6">
                            <button type="submit"
                                class="px-4 py-2 bg-green-500 rounded-md hover:bg-green-700">Assign</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                            
            </div>
        </div>
    </div>
</x-admin-layout>