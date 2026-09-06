<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Usuario o Correo')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4" x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Contraseña')" />

            <div class="mt-1 flex items-center gap-2">
                <div class="flex-1">
                    <x-text-input id="password"
                                class="block w-full"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                name="password"
                                required autocomplete="current-password" />
                </div>

                <button type="button"
                        x-on:click="showPassword = !showPassword"
                        x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                        class="!bg-white !border-gray-300 !text-gray-500 hover:!bg-white hover:!text-gray-500 flex h-10 w-10 items-center justify-center rounded-md border border-gray-300 focus:outline-none">
                    <svg x-show="!showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg x-show="showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">
                        <path d="M3 3l18 18"></path>
                        <path d="M10.58 10.58A2 2 0 0 0 13.42 13.42"></path>
                        <path d="M9.88 5.08A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a17.5 17.5 0 0 1-4.79 6.14M6.61 6.61A17.6 17.6 0 0 0 2 12s3.5 7 10 7a10.85 10.85 0 0 0 5.39-1.61"></path>
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-orange-500 shadow-sm focus:ring-orange-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Guardar Contraseña') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Iniciar session') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
