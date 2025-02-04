<?php

namespace ArtisanBuild\Verbstream\Adverbs;

trait DispatchesAsLaravelEvent
{

    public function fired()
    {
        VerbsEvent::dispatch(static::class, (array) $this);
    }
}
