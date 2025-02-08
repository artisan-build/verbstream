<?php

use ArtisanBuild\Verbstream\Http\Controllers\EmailVerificationNotificationController;
use Illuminate\Support\Facades\Route;

// ... existing routes ...

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');
