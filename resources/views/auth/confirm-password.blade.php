<x-app-layout>
    <x-verbstream::authentication-card>


        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <x-verbstream::validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div>
                <x-verbstream::label for="password" value="{{ __('Password') }}" />
                <flux:input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" autofocus />
            </div>

            <div class="flex justify-end mt-4">
                <flux:button type="submit">
                    {{ __('Confirm') }}
                </flux:button>
            </div>
        </form>
    </x-verbstream::authentication-card>
</x-app-layout>
