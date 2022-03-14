<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Contracts\UserServiceContract as UserService;
use App\Http\Requests\SaveUser;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\UpdateDataFailedException;
use App\Http\Requests\SaveParentEmail;

class AuthController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getProfile()
    {
        $data = $this->userService->getProfile();
        $response = ['error' => false, 'data'=> $data];

        return response()->json($response);
    }

    public function updateParentEmail(SaveParentEmail $request)
    {
        $data = $request->validated();

        $user = auth()->user();
        $check = User::where('id', '!=', $user->id)->where('email', $data['email'])->first();

        if(!$check) {
            return response()->json([
                'error' => true,
                'message' => 'Account not found',
            ], 404);
        }

        $user->parent_id = $check->id;
        $user->save();

        return response()->json([
            'error' => false,
            'message' => 'Updated',
            'data' => $user,
        ]);
    }
}
