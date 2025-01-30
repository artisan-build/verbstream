<?php

namespace ArtisanBuild\Verbstream\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Show the user profile screen.
     *
     * @return View
     */
    public function show(Request $request)
    {
        view()->share('title', 'Edit Your Profile');

        return view('verbstream::profile.show', [
            'request' => $request,
            'user' => $request->user(),
        ]);
    }
}
