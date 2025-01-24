<flux:dropdown position="top" align="start">
    <flux:profile :avatar="auth()->user()->profile_photo_url" />

    <flux:menu>
        <flux:menu.radio.group>
            <flux:menu.radio checked>{{ auth()->user()->name }}</flux:menu.radio>
        </flux:menu.radio.group>

        <flux:menu.separator />
        <flux:menu.item href="{{ route('profile.show') }}">Edit Profile</flux:menu.item>

        @if (ArtisanBuild\Verbstream\Verbstream::hasApiFeatures())
            <flux:menu.item :href="route('api-tokens.index')">API Keys</flux:menu.item>
        @endif
        <flux:menu.separator/>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <flux:menu.item type="submit">Log Out</flux:menu.item>
        </form>

    </flux:menu>
</flux:dropdown>
