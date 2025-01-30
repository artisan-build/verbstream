<x-app-layout>
    <x-verbstream::authentication-card>

        <x-verbstream::validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="block">
                <x-verbstream::label for="email" value="{{ __('Email') }}" />
                <flux:input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-verbstream::label for="password" value="{{ __('Password') }}" />
                <flux:input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-verbstream::label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <flux:input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <flux:button type="submit">
                    {{ __('Reset Password') }}
                </flux:button>
            </div>
        </form>
    </x-verbstream::authentication-card>
</x-app-layout>
