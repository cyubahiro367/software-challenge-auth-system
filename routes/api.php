<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Registration Routes
    Route::controller(RegistrationController::class)
        ->prefix('register')
        ->where(['identifier' => '[0-9a-fA-F-]{36}']) // Explicit UUID format
        ->group(function () {
            Route::post('/start', 'start')->name('register.start');
            Route::get('/resume/{identifier}', 'resume')->name('register.resume');

            Route::post('/step-1/{identifier}', 'step1')->name('register.step1');
            Route::post('/step-2/{identifier}', 'step2')->name('register.step2');
            Route::post('/step-3/{identifier}', 'step3')->name('register.step3');
            Route::post('/verify-2fa/{identifier}', 'verify2FA')->name('register.verify2fa');
            Route::post('/step-4/{identifier}', 'step4')->name('register.step4');
            Route::post('/step-5/{identifier}', 'step5')->name('register.step5');

            Route::post('/complete/{identifier}', 'complete')->name('register.complete');
            Route::post('/upload/profile-picture/{identifier}', 'uploadProfilePicture')->name('register.uploadProfile');
        });
});
