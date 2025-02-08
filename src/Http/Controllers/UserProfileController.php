<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use ArtisanBuild\Verbstream\Events\ProfileInformationUpdated;
use ArtisanBuild\Verbstream\Events\ProfilePhotoUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserProfileController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        ProfileInformationUpdated::commit(
            user_id: $request->user()->id,
            name: $request->name,
            email: $request->email
        );

        return back(303);
    }

    /**
     * Update the user's profile photo.
     *
     * @return RedirectResponse
     */
    public function updatePhoto(Request $request)
    {
        $photo = $request->file('photo');

        ProfilePhotoUpdated::commit(
            user_id: $request->user()->id,
            photo_path: $photo->path(),
            photo_name: $photo->getClientOriginalName(),
            photo_content: base64_encode(file_get_contents($photo->path()))
        );

        return back(303);
    }
}
