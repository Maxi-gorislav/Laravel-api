<?php

namespace App\Http\Controllers;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Models\User;

class AuthController extends AuthenticatedSessionController
{
    public function store(LoginRequest $request)
    {
        $user = User::where('login', $request->login)->first();
        if (!is_null($user) && $user->isApiUser()) {
            return back()->withErrors(__('auth.failed'));
        }
        return $this->loginPipeline($request)->then(function ($request) {
            return app(LoginResponse::class);
        });
    }
}
