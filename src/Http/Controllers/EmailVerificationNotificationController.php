<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use ArtisanBuild\Verbstream\Events\EmailVerificationNotificationSent;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse;
use Laravel\Fortify\Http\Responses\RedirectAsIntended;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|Responsable
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                        ? new JsonResponse('', 204)
                        : app(RedirectAsIntended::class, ['name' => 'email-verification']);
        }

        EmailVerificationNotificationSent::fire(user_id: $request->user()->id);

        return app(EmailVerificationNotificationSentResponse::class);
    }
}
