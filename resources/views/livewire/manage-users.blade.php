<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Add User Form -->
                <div class="mb-8 bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New User</h3>
                    <form wire:submit.prevent="createUser">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-label for="name" value="Name" />
                                <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" required />
                                <x-input-error for="name" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="email" value="Email" />
                                <x-input id="email" type="email" class="mt-1 block w-full" wire:model="email" required />
                                <x-input-error for="email" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="password" value="Password" />
                                <x-input id="password" type="password" class="mt-1 block w-full" wire:model="password" required />
                                <x-input-error for="password" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="role" value="Role" />
                                <select id="role" wire:model="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <x-input-error for="role" class="mt-2" />
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <x-button>
                                {{ __('Create User') }}
                            </x-button>
                        </div>
                    </form>
                </div>

                <!-- Users List -->
                <div>
                     <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Users</h3>
                     <div class="overflow-x-auto border rounded-xl shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registered</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($user->id !== auth()->id())
                                                <button wire:click="confirmUserDeletion({{ $user->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                            @else
                                                <span class="text-gray-400 cursor-not-allowed">Delete</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <x-dialog-modal wire:model.live="confirmingUserDeletion">
                    <x-slot name="title">
                        {{ __('Delete User') }}
                    </x-slot>

                    <x-slot name="content">
                        {{ __('Are you sure you want to delete this user? This action cannot be undone.') }}
                    </x-slot>

                    <x-slot name="footer">
                        <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                            {{ __('Delete User') }}
                        </x-danger-button>
                    </x-slot>
                </x-dialog-modal>

            </div>
        </div>
    </div>
</div>
