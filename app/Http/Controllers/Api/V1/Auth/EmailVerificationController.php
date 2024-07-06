<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class EmailVerificationController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Verify your email address.']);
    }
    /**
     * Verify email
     *
     * @urlParam id int required The ID of the user. Example: 1
     * @urlParam hash string required The email verification hash. Example: e3afed0047b08059d0fada10f400c1e5
     *
     * @response 200 {
     *  "message": "Email verified successfully"
     * }
     * @response 400 {
     *  "message": "Invalid verification link"
     * }
     * @response 400 {
     *  "message": "Email already verified"
     * }
     * @response 403 {
     *  "message": "Unauthorized"
     * }
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = Auth::user();

        if ($user->id != $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified successfully']);
    }

    /**
     * Resend email verification
     *
     * @authenticated
     *
     * @response 200 {
     *  "message": "Verification email resent"
     * }
     * @response 400 {
     *  "message": "Email already verified"
     * }
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email resent']);
    }
}
