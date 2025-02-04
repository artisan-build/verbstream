<?php

use ArtisanBuild\Verbstream\Contracts\CreatesUsers;
use ArtisanBuild\Verbstream\Enums\TeamLabels;
use ArtisanBuild\Verbstream\Events\UserCreated;
use ArtisanBuild\Verbstream\Features;
use ArtisanBuild\Verbstream\Http\Middleware\AuthenticateSession;

return [
    'team_label' => TeamLabels::Team,

    'events' => [
        CreatesUsers::class => UserCreated::class,
    ],

    'middleware' => ['web'],

    'auth_session' => AuthenticateSession::class,

    'guard' => 'sanctum',

    'features' => [
        Features::termsAndPrivacyPolicy(),
        Features::profilePhotos(),
        Features::api(),
        Features::teams(['invitations' => true]),
        Features::accountDeletion(),
    ],

    'profile_photo_disk' => 'public',

];
