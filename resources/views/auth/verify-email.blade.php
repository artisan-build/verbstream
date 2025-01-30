<x-app-layout>
    <x-verbstream::authentication-card>

        <flux:text>
            {{ __('Before continuing, please verify your email address by clicking on the link we just emailed to you. If you didn\'t receive the email, we will gladly send you another.') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text>
                {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
            </flux:text>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <flux:button type="submit">
                        {{ __('Resend Verification Email') }}
                    </flux:button>
                </div>
            </form>

            <div>
                <flux:button variant="subtle"
                    href="{{ route('profile.show') }}"
                >
                    {{ __('Edit Profile') }}</flux:button>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf

                    <flux:button type="submit" variant="subtle">
                        {{ __('Log Out') }}
                    </flux:button>
                </form>
            </div>
        </div>
    </x-verbstream::authentication-card>
</x-app-layout>
