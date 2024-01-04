<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    //
    public function user(Request $request){
        $user = null;
        if($request->name){
            $user = User::where('name', $request->name)->first();
        }else if(auth('sanctum')->user() && !$request->name){
            $user = auth('sanctum')->user();
        }

        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['data' => new UserResource($user)], 200);
    }
}
