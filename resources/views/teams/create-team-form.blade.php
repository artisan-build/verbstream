<x-verbstream::form-section submit="createTeam">
    <x-slot name="title">
        {{ __('Team Details') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Create a new team to collaborate with others on projects.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <x-verbstream::label value="{{ __('Team Owner') }}" />

            <div class="flex items-center mt-2">
                <img class="size-12 rounded-full object-cover" src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}">

                <div class="ms-4 leading-tight">
                    <div class="text-gray-900 dark:text-white">{{ $this->user->name }}</div>
                    <div class="text-gray-700 dark:text-gray-300 text-sm">{{ $this->user->email }}</div>
                </div>
            </div>
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-verbstream::label for="name" value="{{ __('Team Name') }}" />
            <flux:input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" autofocus />
            <x-verbstream::input-error for="name" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <flux:button type="submit">
            {{ __('Create') }}
        </flux:button>
    </x-slot>
</x-verbstream::form-section>
