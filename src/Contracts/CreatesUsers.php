<?php

namespace ArtisanBuild\Verbstream\Contracts;

use Illuminate\Foundation\Auth\User;

interface CreatesUsers
{
    public function handle(): User;
}
