<?php

namespace ArtisanBuild\Verbstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InvitingTeamMember
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $team
     * @param  mixed  $email
     * @param  mixed  $role
     * @return void
     */
    public function __construct(
        /**
         * The team instance.
         */
        public $team,
        /**
         * The email address of the invitee.
         */
        public $email,
        /**
         * The role of the invitee.
         */
        public $role
    )
    {
    }
}
