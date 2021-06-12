<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Jetstream;

class AuthController extends Controller
{
    public function token(Request $request)
    {
        $user = User::where('login', $request->login)->first();
        if (!$user || !Hash::check($request->password, $user->password) || !$user->isApiUser()) {
            return response()->json(['code' => 401, 'data' => 'Unauthenticated']);
        }
        
        $user->tokens()->delete();
        $token = $user->createToken(
            $request->login, Jetstream::validPermissions($request->input('permissions', ['read', 'create', 'delete', 'update']))
        );
        
        return response()->json(['token' => explode('|', $token->plainTextToken, 2)[1],]);
    }
}
