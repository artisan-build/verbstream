<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ProfilePhotoUpdated extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $photo_path;

    public string $photo_name;

    public string $photo_content;

    public function apply(UserState $state): void
    {
        // No state changes needed as photo path is stored in user record
    }

    public function handle(): User
    {
        $user = User::findOrFail($this->user_id);

        // Create temporary file from content
        $tempFile = tempnam(sys_get_temp_dir(), 'profile_photo_');
        file_put_contents($tempFile, base64_decode($this->photo_content));

        // Create UploadedFile instance for validation
        $uploadedFile = new UploadedFile(
            $tempFile,
            $this->photo_name,
            null,
            null,
            true
        );

        // Validate the photo
        Validator::make(['photo' => $uploadedFile], [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfilePhoto');

        // Delete old photo if it exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Store the new photo
        $path = Storage::disk('public')->putFileAs(
            'profile-photos',
            $uploadedFile,
            $this->photo_name
        );

        if (! $path) {
            throw new RuntimeException('Failed to store profile photo.');
        }

        // Clean up temporary file
        @unlink($tempFile);

        // Update user's photo path
        $user->forceFill([
            'profile_photo_path' => $path,
        ])->save();

        return $user->fresh();
    }
}
