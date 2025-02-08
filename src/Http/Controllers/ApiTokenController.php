<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use ArtisanBuild\Verbstream\Events\ApiTokenCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiTokenController extends Controller
{
    /**
     * Create a new API token.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $token = ApiTokenCreated::commit(
            user_id: $request->user()->id,
            name: $request->name,
            abilities: $request->abilities ?? []
        );

        return response()->json(['token' => $token->plainTextToken]);
    }
} 