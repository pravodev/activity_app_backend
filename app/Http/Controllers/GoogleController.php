<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use App\Models\User;
use App\Models\PointTransaction;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google ')->stateless()->redirect();
    }

    public function callback(Request $request)
    {
        // $oauth = Socialite::driver('google')->userFromToken($request->state);
        // dd($oauth);
        $oauthUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $oauthUser->id)->first();
        if ($user) {
            $user->avatar = $oauthUser->avatar;
            $user->name = $oauthUser->name;
            $user->save();
        } else {
            $user = User::create([
                'name' => $oauthUser->name ?: $oauthUser->email,
                'email' => $oauthUser->email,
                'google_id'=> $oauthUser->id,
                'avatar' => $oauthUser->avatar,
            ]);
        }

        $token = auth()->guard('api')->login($user);
        return response()->json([
            'token' => $token,
            'data' => $user
        ]);
    }
}
