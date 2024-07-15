<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['logout']); // Adjust for your authentication method
    }

    /**
     * Register
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: johndoe@example.com
     * @bodyParam phone string required The phone number of the user in international format. Example: +36123456789
     * @bodyParam password string required The password of the user. Example: secretpassword
     *
     * @response 201 {
     *  "token": "1|sometokenstring"
     * }
     */
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['password'] = Hash::make($request->password);

        $user = User::create($validatedData);

        event(new Registered($user));

        $user->sendEmailVerificationNotification(); 

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    /**
     * Login
     *
     * @bodyParam email string required The email of the user. Example: johndoe@example.com
     * @bodyParam password string required The password of the user. Example: secretpassword
     *
     * @response 200 {
     *  "token": "1|sometokenstring"
     * }
     * @response 401 {
     *  "message": "Unauthorized"
     * }
     */

     public function login(Request $request)
     {
        $credentials = $request->validate([
            'email' => 'required|string|email', // |exists:users,email
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => Lang::get('http.401')], 401); // Unauthorized
        }

        $user = $request->user();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    /**
     * Logout
     *
     * @authenticated
     *
     * @response 200 {
     *  "message": "Logged out"
     * }
     */
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out', 'user' => auth()->user()], 200); // OK
    }
}
