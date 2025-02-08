<?php

namespace ArtisanBuild\Verbstream\Actions;

use App\Models\User;
use ArtisanBuild\Verbstream\Events\EmailVerificationNotificationSent;
use ArtisanBuild\Verbstream\Events\UserCreated;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    private bool $shouldSendVerificationEmail = true;

    public function withoutVerificationEmail(): self
    {
        $this->shouldSendVerificationEmail = false;

        return $this;
    }

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Verbstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = UserCreated::commit(
            name: $input['name'],
            email: $input['email'],
            password: Hash::make($input['password']),
        );

        if ($user instanceof MustVerifyEmail && $this->shouldSendVerificationEmail) {
            /** @phpstan-ignore-next-line  */
            EmailVerificationNotificationSent::fire(user_id: $user->id);
        }

        return $user;
    }
}
