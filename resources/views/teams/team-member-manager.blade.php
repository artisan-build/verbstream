<div>
    @if (Gate::check('addTeamMember', $team))
        <flux:separator/>

        <!-- Add Team Member -->
        <div class="mt-10 sm:mt-0">
            <x-verbstream::form-section submit="addTeamMember">
                <x-slot name="title">
                    {{ __('Add Team Member') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Add a new team member to your team, allowing them to collaborate with you.') }}
                </x-slot>

                <x-slot name="form">
                    <div class="col-span-6">
                        <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Please provide the email address of the person you would like to add to this team.') }}
                        </div>
                    </div>

                    <!-- Member Email -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-verbstream::label for="email" value="{{ __('Email') }}"/>
                        <flux:input id="email" type="email" class="mt-1 block w-full"
                                 wire:model="addTeamMemberForm.email"/>
                        <x-verbstream::input-error for="email" class="mt-2"/>
                    </div>

                    <!-- Role -->
                    @if (count($this->roles) > 0)
                        <div class="col-span-6 lg:col-span-4">
                            <x-verbstream::label for="role" value="{{ __('Role') }}"/>
                            <x-verbstream::input-error for="role" class="mt-2"/>

                            <div class="relative z-0 mt-1 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer">
                                @foreach ($this->roles as $index => $role)
                                    <button type="button"
                                            class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-hidden focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 {{ $index > 0 ? 'border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none' : '' }} {{ ! $loop->last ? 'rounded-b-none' : '' }}"
                                            wire:click="$set('addTeamMemberForm.role', '{{ $role->key }}')">
                                        <div class="{{ isset($addTeamMemberForm['role']) && $addTeamMemberForm['role'] !== $role->key ? 'opacity-50' : '' }}">
                                            <!-- Role Name -->
                                            <div class="flex items-center">
                                                <div class="text-sm text-gray-600 dark:text-gray-400 {{ $addTeamMemberForm['role'] == $role->key ? 'font-semibold' : '' }}">
                                                    {{ $role->name }}
                                                </div>

                                                @if ($addTeamMemberForm['role'] == $role->key)
                                                    <svg class="ms-2 size-5 text-green-400"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </div>

                                            <!-- Role Description -->
                                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 text-start">
                                                {{ $role->description }}
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-slot>

                <x-slot name="actions">
                    <x-verbstream::action-message class="me-3" on="saved">
                        {{ __('Added.') }}
                    </x-verbstream::action-message>

                    <flux:button type="submit">
                        {{ __('Add') }}
                    </flux:button>
                </x-slot>
            </x-verbstream::form-section>
        </div>
    @endif

    @if ($team->teamInvitations->isNotEmpty() && Gate::check('addTeamMember', $team))
        <flux:separator/>

        <!-- Team Member Invitations -->
        <div class="mt-10 sm:mt-0">
            <x-verbstream::action-section>
                <x-slot name="title">
                    {{ __('Pending Team Invitations') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('These people have been invited to your team and have been sent an invitation email. They may join the team by accepting the email invitation.') }}
                </x-slot>

                <x-slot name="content">
                    <div class="space-y-6">
                        @foreach ($team->teamInvitations as $invitation)
                            <div class="flex items-center justify-between">
                                <div class="text-gray-600 dark:text-gray-400">{{ $invitation->email }}</div>

                                <div class="flex items-center">
                                    @if (Gate::check('removeTeamMember', $team))
                                        <!-- Cancel Team Invitation -->
                                        <button class="cursor-pointer ms-6 text-sm text-red-500 focus:outline-hidden"
                                                wire:click="cancelTeamInvitation({{ $invitation->id }})">
                                            {{ __('Cancel') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-verbstream::action-section>
        </div>
    @endif

    @if ($team->users->isNotEmpty())
        <flux:separator/>

        <!-- Manage Team Members -->
        <div class="mt-10 sm:mt-0">
            <x-verbstream::action-section>
                <x-slot name="title">
                    {{ __('Team Members') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('All of the people that are part of this team.') }}
                </x-slot>

                <!-- Team Member List -->
                <x-slot name="content">
                    <div class="space-y-6">
                        @foreach ($team->users->sortBy('name') as $user)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img class="size-8 rounded-full object-cover" src="{{ $user->profile_photo_url }}"
                                         alt="{{ $user->name }}">
                                    <div class="ms-4 dark:text-white">{{ $user->name }}</div>
                                </div>

                                <div class="flex items-center">
                                    <!-- Manage Team Member Role -->
                                    @if (Gate::check('updateTeamMember', $team) && ArtisanBuild\Verbstream\Verbstream::hasRoles())
                                        <button class="ms-2 text-sm text-gray-400 underline"
                                                wire:click="manageRole('{{ $user->id }}')">
                                            {{ ArtisanBuild\Verbstream\Verbstream::findRole($user->membership->role)->name }}
                                        </button>
                                    @elseif (ArtisanBuild\Verbstream\Verbstream::hasRoles())
                                        <div class="ms-2 text-sm text-gray-400">
                                            {{ ArtisanBuild\Verbstream\Verbstream::findRole($user->membership->role)->name }}
                                        </div>
                                    @endif

                                    <!-- Leave Team -->
                                    @if ($this->user->id === $user->id)
                                        <button class="cursor-pointer ms-6 text-sm text-red-500"
                                                wire:click="$toggle('confirmingLeavingTeam')">
                                            {{ __('Leave') }}
                                        </button>

                                        <!-- Remove Team Member -->
                                    @elseif (Gate::check('removeTeamMember', $team))
                                        <button class="cursor-pointer ms-6 text-sm text-red-500"
                                                wire:click="confirmTeamMemberRemoval('{{ $user->id }}')">
                                            {{ __('Remove') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-verbstream::action-section>
        </div>
    @endif

    <!-- Role Management Modal -->
    <x-verbstream::dialog-modal wire:model.live="currentlyManagingRole">
        <x-slot name="title">
            {{ __('Manage Role') }}
        </x-slot>

        <x-slot name="content">
            <div class="relative z-0 mt-1 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer">
                @foreach ($this->roles as $index => $role)
                    <button type="button"
                            class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-hidden focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 {{ $index > 0 ? 'border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none' : '' }} {{ ! $loop->last ? 'rounded-b-none' : '' }}"
                            wire:click="$set('currentRole', '{{ $role->key }}')">
                        <div class="{{ $currentRole !== $role->key ? 'opacity-50' : '' }}">
                            <!-- Role Name -->
                            <div class="flex items-center">
                                <div class="text-sm text-gray-600 dark:text-gray-400 {{ $currentRole == $role->key ? 'font-semibold' : '' }}">
                                    {{ $role->name }}
                                </div>

                                @if ($currentRole == $role->key)
                                    <svg class="ms-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>

                            <!-- Role Description -->
                            <div class="mt-2 text-xs text-gray-600 text-start dark:text-gray-400">
                                {{ $role->description }}
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <flux:button wire:click="stopManagingRole" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </flux:button>

            <flux:button variant="primary" class="ms-3" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('Save') }}
            </flux:button>
        </x-slot>
    </x-verbstream::dialog-modal>

    <!-- Leave Team Confirmation Modal -->
    <x-verbstream::confirmation-modal wire:model.live="confirmingLeavingTeam">
        <x-slot name="title">
            {{ __('Leave Team') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to leave this team?') }}
        </x-slot>

        <x-slot name="footer">
            <flux:button wire:click="$toggle('confirmingLeavingTeam')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </flux:button>

            <flux:button variant="primary" class="ms-3" wire:click="leaveTeam" wire:loading.attr="disabled">
                {{ __('Leave') }}
            </flux:button>
        </x-slot>
    </x-verbstream::confirmation-modal>

    <!-- Remove Team Member Confirmation Modal -->
    <x-verbstream::confirmation-modal wire:model.live="confirmingTeamMemberRemoval">
        <x-slot name="title">
            {{ __('Remove Team Member') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to remove this person from the team?') }}
        </x-slot>

        <x-slot name="footer">
            <flux:button wire:click="$toggle('confirmingTeamMemberRemoval')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </flux:button>

            <flux:button variant="primary" class="ms-3" wire:click="removeTeamMember" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </flux:button>
        </x-slot>
    </x-verbstream::confirmation-modal>
</div>
