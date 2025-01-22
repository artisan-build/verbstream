<?php

namespace ArtisanBuild\Verbstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AddingTeam
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $owner
     * @return void
     */
    public function __construct(
        /**
         * The team owner.
         */
        public $owner
    ) {}
}
