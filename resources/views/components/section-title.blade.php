<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <flux:heading level="3" class="!text-lg font-medium">{{ $title }}</flux:heading>

        <flux:subheading>
            {{ $description }}
        </flux:subheading>
    </div>

    <flux:text>
        {{ $aside ?? '' }}
    </flux:text>
</div>
