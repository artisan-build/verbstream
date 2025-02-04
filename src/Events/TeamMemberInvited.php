<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use ArtisanBuild\Verbstream\Mail\TeamInvitation;
use Illuminate\Support\Facades\Mail;
use Thunk\Verbs\Event;
use Thunk\Verbs\Facades\Verbs;

class TeamMemberInvited extends Event
{
    public int $team_id;

    public string $email;

    public ?string $role = null;

    public function handle()
    {
        $invitation = Team::find($this->team_id)?->teamInvitations()->create([
            'email' => $this->email,
            'role' => $this->role,
        ]);

        Verbs::unlessReplaying(fn () => /** @var \App\Models\TeamInvitation $invitation */
            Mail::to($this->email)->send(new TeamInvitation($invitation)));

    }
}
