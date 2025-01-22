<?php

namespace ArtisanBuild\Verbstream\Http\Controllers\Livewire;

use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TermsOfServiceController extends Controller
{
    /**
     * Show the terms of service for the application.
     *
     * @return View
     */
    public function show(Request $request)
    {
        $termsFile = Verbstream::localizedMarkdownPath('terms.md');

        return view('terms', [
            'terms' => Str::markdown(file_get_contents($termsFile)),
        ]);
    }
}
