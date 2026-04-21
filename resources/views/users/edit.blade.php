<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Contraseña (dejar en blanco para no cambiar)') }}</label>
                            <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirmar Contraseña') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('password_confirmation') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">{{ __('Rol') }}</label>
                            <select name="role" id="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('Seleccionar rol') }}</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('Administrador') }}</option>
                                <option value="tecnico" {{ old('role', $user->role) == 'tecnico' ? 'selected' : '' }}>{{ __('Técnico') }}</option>
                                <option value="contable" {{ old('role', $user->role) == 'contable' ? 'selected' : '' }}>{{ __('Contable') }}</option>
                                <option value="auditor" {{ old('role', $user->role) == 'auditor' ? 'selected' : '' }}>{{ __('Auditor') }}</option>
                            </select>
                            @error('role') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                {{ __('Actualizar Usuario') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
