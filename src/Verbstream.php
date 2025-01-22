<?php

namespace ArtisanBuild\Verbstream;

use App\Models\User;
use ArtisanBuild\Verbstream\Contracts\AddsTeamMembers;
use ArtisanBuild\Verbstream\Contracts\CreatesTeams;
use ArtisanBuild\Verbstream\Contracts\DeletesTeams;
use ArtisanBuild\Verbstream\Contracts\DeletesUsers;
use ArtisanBuild\Verbstream\Contracts\InvitesTeamMembers;
use ArtisanBuild\Verbstream\Contracts\RemovesTeamMembers;
use ArtisanBuild\Verbstream\Contracts\UpdatesTeamNames;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/* Why we used final here...
 * We don't use final as a rule. However, in this case we are using static methods to set static properties to be used
 * across this package, and we want to make sure that we are always working with an instance of this class. Jetstream
 * used `return new static` which theoretically would have allowed for this class to be extended, but doing so would
 * very likely have broken some things. There is simply no world in which extending this particular class makes sense
 * and the safest way to make sure that never happens is to simply mark it final.
 *
 * We're not ready to become a "final by default" shop, but it does have its places and this is one of them.
*/
final class Verbstream
{
    public static bool $registersRoutes = true;

    public static array $roles = [];

    public static array $permissions = [];

    public static array $defaultPermissions = [];

    public static string $userModel = User::class;

    public static string $teamModel = \App\Models\Team::class;

    public static string $membershipModel = \App\Models\Membership::class;

    public static string $teamInvitationModel = \App\Models\TeamInvitation::class;

    public static function hasRoles(): bool
    {
        return count(self::$roles) > 0;
    }

    /**
     * Find the role with the given key.
     *
     * @return Role
     */
    public static function findRole(string $key): ?Role
    {
        return self::$roles[$key] ?? null;
    }

    public static function role(string $key, string $name, array $permissions): Role
    {
        self::$permissions = collect(array_merge(self::$permissions, $permissions))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return tap(new Role($key, $name, $permissions), static function ($role) use ($key): void {
            Verbstream::$roles[$key] = $role;
        });
    }

    public static function hasPermissions(): bool
    {
        return count(self::$permissions) > 0;
    }

    public static function permissions(array $permissions): Verbstream
    {
        self::$permissions = $permissions;

        return new self;
    }

    public static function defaultApiTokenPermissions(array $permissions): Verbstream
    {
        self::$defaultPermissions = $permissions;

        return new self;
    }

    public static function validPermissions(array $permissions): array
    {
        return array_values(array_intersect($permissions, self::$permissions));
    }

    public static function managesProfilePhotos(): bool
    {
        return Features::managesProfilePhotos();
    }

    public static function hasApiFeatures(): bool
    {
        return Features::hasApiFeatures();
    }

    public static function hasTeamFeatures(): bool
    {
        return Features::hasTeamFeatures();
    }

    public static function userHasTeamFeatures($user): bool
    {
        return (array_key_exists(HasTeams::class, class_uses_recursive($user)) ||
                method_exists($user, 'currentTeam')) &&
                self::hasTeamFeatures();
    }

    public static function hasTermsAndPrivacyPolicyFeature(): bool
    {
        return Features::hasTermsAndPrivacyPolicyFeature();
    }

    public static function hasAccountDeletionFeatures(): bool
    {
        return Features::hasAccountDeletionFeatures();
    }

    public static function findUserByIdOrFail($id): Authenticatable
    {
        /** @phpstan-ignore-next-line  */
        return self::newUserModel()->where('id', $id)->firstOrFail();
    }

    public static function findUserByEmailOrFail(string $email): Authenticatable
    {
        /** @phpstan-ignore-next-line  */
        return self::newUserModel()->where('email', $email)->firstOrFail();
    }

    public static function userModel(): string
    {
        return self::$userModel;
    }

    public static function newUserModel(): Authenticatable
    {
        $model = self::userModel();

        return new $model;
    }

    public static function useUserModel(string $model): Verbstream
    {
        self::$userModel = $model;

        return new self;
    }

    public static function teamModel(): string
    {
        return self::$teamModel;
    }

    public static function newTeamModel(): mixed
    {
        $model = self::teamModel();

        return new $model;
    }

    public static function useTeamModel(string $model): Verbstream
    {
        self::$teamModel = $model;

        return new self;
    }

    public static function membershipModel(): string
    {
        return self::$membershipModel;
    }

    public static function useMembershipModel(string $model): Verbstream
    {
        self::$membershipModel = $model;

        return new self;
    }

    public static function teamInvitationModel(): string
    {
        return self::$teamInvitationModel;
    }

    public static function localizedMarkdownPath($name): string
    {
        $localName = preg_replace('#(\.md)$#i', '.'.app()->getLocale().'$1', (string) $name);

        return Arr::first([
            resource_path('markdown/'.$localName),
            resource_path('markdown/'.$name),
        ], static fn($path) => file_exists($path));
    }


    public static function ignoreRoutes(): Verbstream
    {
        self::$registersRoutes = false;

        return new self;
    }
}
