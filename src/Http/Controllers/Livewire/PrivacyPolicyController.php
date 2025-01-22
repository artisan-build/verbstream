<?php

namespace ArtisanBuild\Verbstream\Http\Controllers\Livewire;

use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrivacyPolicyController extends Controller
{
    /**
     * Show the privacy policy for the application.
     *
     * @return View
     */
    public function show(Request $request)
    {
        $policyFile = Verbstream::localizedMarkdownPath('policy.md');

        return view('policy', [
            'policy' => Str::markdown(file_get_contents($policyFile)),
        ]);
    }
}
