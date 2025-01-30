<x-verbstream::form-section submit="updatePassword">
    <x-slot name="title">
        {{ __('Update Password') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <flux:input :label="__('Current Password')" id="current_password" type="password" class="mt-1 block w-full" wire:model="state.current_password" autocomplete="current-password" />
            <x-verbstream::input-error for="current_password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <flux:input :label="__('New Password')" id="password" type="password" class="mt-1 block w-full" wire:model="state.password" autocomplete="new-password" />
            <x-verbstream::input-error for="password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <flux:input :label="__('Confirm Password')" id="password_confirmation" type="password" class="mt-1 block w-full" wire:model="state.password_confirmation" autocomplete="new-password" />
            <x-verbstream::input-error for="password_confirmation" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-verbstream::action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-verbstream::action-message>

        <flux:button type="submit">
            {{ __('Save') }}
        </flux:button>
    </x-slot>
</x-verbstream::form-section>
