<?php

namespace ArtisanBuild\Verbstream\Adverbs;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerbsEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public string $event, public array $properties) {
    }
}
