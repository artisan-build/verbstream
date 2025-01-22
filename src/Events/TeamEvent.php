<?php

namespace ArtisanBuild\Verbstream\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class TeamEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Team  $team
     * @return void
     */
    public function __construct(
        /**
         * The team instance.
         */
        public $team
    ) {}
}
