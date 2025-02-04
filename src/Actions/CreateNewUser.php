<?php

namespace ArtisanBuild\Verbstream\Actions;

use App\Models\User;
use ArtisanBuild\Verbstream\Events\UserCreated;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

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

        return UserCreated::commit(
            name: $input['name'],
            email: $input['email'],
            password: Hash::make($input['password']),
        );
    }
}
