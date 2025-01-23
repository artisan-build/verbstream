<x-app-layout>
    <x-authentication-card>
        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <flux:input id="name" name="name" type="text" label="Name" :value="old('name')"  required autofocus autocomplete="name"/>

            <flux:input id="email" name="email" type="email" label="Email" :value="old('email')"  required autofocus autocomplete="username"/>

            <flux:input type="password" label="Password" id="password" name="password" required autocomplete="new-password"/>

            <flux:input type="password" label="Confirm Password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"/>


            @if (ArtisanBuild\Verbstream\Verbstream::hasTermsAndPrivacyPolicyFeature())
                <flux:field class="inline-flex space-x-2">
                    <flux:checkbox name="terms" id="terms" required />
                    <label for="terms">I agree to the <flux:link :href="route('terms.show')">terms of service</flux:link> and <flux:link :href="route('policy.show')">privacy policy</flux:link></label>

                </flux:field>
            @endif

            <div class="flex items-center justify-end mt-4 space-x-12">
                <flux:link :href="route('login')">{{ __('Already registered?') }}</flux:link>

                <flux:button type="submit" variant="primary">
                    {{ __('Register') }}
                </flux:button>
            </div>
        </form>
    </x-authentication-card>
</x-app-layout>
