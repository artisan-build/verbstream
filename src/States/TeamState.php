<?php

namespace ArtisanBuild\Verbstream\States;

use Thunk\Verbs\State;

class TeamState extends State
{
    public string $name;
    public int $user_id;
    public bool $personal_team;
} 