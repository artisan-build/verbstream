<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use ArtisanBuild\Verbstream\Events\ProfileInformationUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserProfileController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param  Request  $request
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
} 