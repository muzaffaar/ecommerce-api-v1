<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class ResetPasswordController extends Controller
{
    /**
     * Reset password
     *
     * @bodyParam token string required The password reset token. Example: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
     * @bodyParam email string required The email address of the user. Example: johndoe@example.com
     * @bodyParam password string required The new password. Example: newpassword123
     * @bodyParam password_confirmation string required Confirmation of the new password. Example: newpassword123
     *
     * @response 200 {
     *  "message": "Your password has been reset!"
     * }
     * @response 422 {
     *  "errors": {
     *    "email": ["This password reset token is invalid."]
     *  }
     * }
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
