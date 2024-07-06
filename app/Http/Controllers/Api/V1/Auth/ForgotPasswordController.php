<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class ForgotPasswordController extends Controller
{
    /**
     * Send password reset link
     *
     * @bodyParam email string required The email address of the user. Example: johndoe@example.com
     *
     * @response 200 {
     *  "message": "We have emailed your password reset link!"
     * }
     * @response 400 {
     *  "message": "We can't find a user with that email address."
     * }
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
}
