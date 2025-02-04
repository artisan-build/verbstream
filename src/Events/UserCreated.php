<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class UserCreated extends Event
{
    #[StateId(UserState::class)]
    public ?int $user_id = null;

    public string $name;

    public string $email;

    public string $password;

    public function apply(UserState $state)
    {
        $state->email = $this->email;
        $state->last_login = Date::now();
    }

    public function handle()
    {
        $user = DB::transaction(fn () => tap(User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ]), static function (User $user): void {
            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0]."'s ".config('verbstream.team_label')->value,
                'personal_team' => true,
            ]));
        }));

        return $user;
    }
}
