<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use ArtisanBuild\Verbstream\Events\ApiTokenCreated;
use ArtisanBuild\Verbstream\Events\ApiTokenDeleted;
use ArtisanBuild\Verbstream\Events\ApiTokenPermissionsUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiTokenController extends Controller
{
    /**
     * Create a new API token.
     *
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

    /**
     * Update the given API token's abilities.
     *
     * @return JsonResponse
     */
    public function update(Request $request, int $tokenId)
    {
        $token = ApiTokenPermissionsUpdated::commit(
            user_id: $request->user()->id,
            token_id: $tokenId,
            abilities: $request->abilities ?? []
        );

        return response()->json(['token' => $token]);
    }

    /**
     * Delete the given API token.
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, int $tokenId)
    {
        ApiTokenDeleted::fire(
            user_id: $request->user()->id,
            token_id: $tokenId
        );

        return response()->json(['message' => 'Token deleted successfully']);
    }
}
