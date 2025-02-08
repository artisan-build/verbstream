<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Events\ProfilePhotoUpdated;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
    Storage::fake('public');
});

test('user can update profile photo', function (): void {
    $user = User::factory()->create();
    $photo = UploadedFile::fake()->image('photo.jpg');

    $updatedUser = ProfilePhotoUpdated::commit(
        user_id: $user->id,
        photo_path: $photo->path(),
        photo_name: $photo->getClientOriginalName(),
        photo_content: base64_encode(file_get_contents($photo->path()))
    );

    // Assert photo was stored
    Storage::disk('public')->assertExists($updatedUser->profile_photo_path);

    // Assert user record was updated
    expect($updatedUser->profile_photo_path)->not->toBeNull()
        ->and($user->fresh()->profile_photo_path)->toBe($updatedUser->profile_photo_path);
});

test('old photo is deleted when updating', function (): void {
    $user = User::factory()->create();
    $oldPhoto = UploadedFile::fake()->image('old.jpg');

    // Upload first photo
    $user = ProfilePhotoUpdated::commit(
        user_id: $user->id,
        photo_path: $oldPhoto->path(),
        photo_name: $oldPhoto->getClientOriginalName(),
        photo_content: base64_encode(file_get_contents($oldPhoto->path()))
    );

    $oldPath = $user->profile_photo_path;

    // Upload new photo
    $newPhoto = UploadedFile::fake()->image('new.jpg');
    $updatedUser = ProfilePhotoUpdated::commit(
        user_id: $user->id,
        photo_path: $newPhoto->path(),
        photo_name: $newPhoto->getClientOriginalName(),
        photo_content: base64_encode(file_get_contents($newPhoto->path()))
    );

    // Assert old photo was deleted and new photo exists
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($updatedUser->profile_photo_path);
});

test('validates photo is an image', function (): void {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf');

    expect(fn () => ProfilePhotoUpdated::commit(
        user_id: $user->id,
        photo_path: $file->path(),
        photo_name: $file->getClientOriginalName(),
        photo_content: base64_encode(file_get_contents($file->path()))
    ))->toThrow(ValidationException::class);

    // Assert no photo was stored
    expect($user->fresh()->profile_photo_path)->toBeNull();
});

test('validates photo size', function (): void {
    $user = User::factory()->create();

    // Create a large file (2MB)
    $tempFile = tempnam(sys_get_temp_dir(), 'large_photo_');
    file_put_contents($tempFile, str_repeat('0', 2 * 1024 * 1024));

    $largePhoto = new UploadedFile(
        $tempFile,
        'large.jpg',
        'image/jpeg',
        null,
        true
    );

    expect(fn () => ProfilePhotoUpdated::commit(
        user_id: $user->id,
        photo_path: $largePhoto->path(),
        photo_name: $largePhoto->getClientOriginalName(),
        photo_content: base64_encode(file_get_contents($largePhoto->path()))
    ))->toThrow(ValidationException::class);

    // Assert no photo was stored
    expect($user->fresh()->profile_photo_path)->toBeNull();

    // Clean up
    @unlink($tempFile);
});
