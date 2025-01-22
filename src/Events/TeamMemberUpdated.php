<?php

namespace ArtisanBuild\Verbstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TeamMemberUpdated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $team
     * @param  mixed  $user
     * @return void
     */
    public function __construct(
        /**
         * The team instance.
         */
        public $team,
        /**
         * The team member that was updated.
         */
        public $user
    ) {}
}
