<?php

namespace ArtisanBuild\Verbstream\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    /**
     * Show the user API token screen.
     *
     * @return View
     */
    public function index(Request $request)
    {
        return view('verbstream::api.index', [
            'request' => $request,
            'user' => $request->user(),
        ]);
    }
}
