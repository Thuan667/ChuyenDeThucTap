<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->json()->get('name'),
            'email' => $request->json()->get('email'),
            'password' => Hash::make($request->json()->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $credentials = $request->json()->all();
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], Response::HTTP_BAD_REQUEST);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = auth()->user();
        return response()->json(compact('user', 'token'));
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], Response::HTTP_NOT_FOUND);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json(['token_absent'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(compact('user'));
    }
    public function show()
    {
        return User::all();
    }
    public function deleteUser($id)
    {
        $user = User::find($id);  // Tìm bản ghi với id
        if ($user) {
            $user->delete();  // Xóa bản ghi nếu tìm thấy
            return response()->json(['message' => 'Xóa thành công!'], 200);
        } else {
            return response()->json(['message' => 'Bản ghi không tồn tại!'], 404);
        }
    }

}
