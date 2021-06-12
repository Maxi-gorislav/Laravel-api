<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserService
{
    public function info()
    {
        $user = null;
        if (Auth::check()) {
            $user = User::find(Auth::user()->id);
        }
        
        return $user;
    }
}