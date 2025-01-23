<x-app-layout>
    <x-authentication-card>
        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <flux:input id="email" name="email" type="email" label="Email" :value="old('email')"  required autofocus autocomplete="username"/>

            <flux:input type="password" label="Password" id="password" name="password" required autocomplete="current-password"/>


            <flux:checkbox id="remember_me" name="remember" :label="__('Remember me')" />


            <div class="flex items-center justify-end mt-4 space-x-12">
                @if (Route::has('password.request'))
                    <flux:link href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif

                <flux:button type="submit" variant="primary">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>
    </x-authentication-card>
</x-app-layout>
