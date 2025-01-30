@props(['submit'])

<div class="md:grid md:grid-cols-3 md:gap-6">

    <x-verbstream::section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-verbstream::section-title>


    <flux:card class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <x-flux::separator variant="subtle"/>
                <div class="flex items-center justify-end px-4 py-3  text-end sm:px-6">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </flux:card>
</div>
