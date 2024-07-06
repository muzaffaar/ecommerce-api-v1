<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class PhoneVerificationController extends Controller
{
    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Send verification code
     *
     * @authenticated
     *
     * @response 200 {
     *  "message": "Verification code has been sent, please check your messenger."
     * }
     * @response 400 {
     *  "message": "Verification code has been sent, please check your messenger."
     * }
     */
    public function sendVerificationCode()
    {
        $user = User::find(auth()->id());
        if (!$user->verification_code) {
            $phoneNumber = auth()->user()->phone;
            $verificationCode = rand(1000, 9999);
            
            $user->verification_code = $verificationCode;
            $user->save();
            
            $this->twilio->sendSms($phoneNumber, "Your verification code is: $verificationCode");
        }
        return response()->json(['phone-message' => 'Verification code has been sent, please check you messanger.'], 400);
        
    }

    /**
     * Resend verification code
     *
     * @authenticated
     *
     * @response 200 {
     *  "message": "Verification code sent."
     * }
     * @response 400 {
     *  "message": "Verification code sent."
     * }
     */
    public function resendVerificationCode(Request $request)
    {
        $user = User::find(auth()->id());
        $user->verification_code = NULL;
        $user->save();

        $phoneNumber = auth()->user()->phone;
        $verificationCode = rand(1000, 9999);
        
        $user = User::find(auth()->id());
        $user->verification_code = $verificationCode;
        $user->save();

        $this->twilio->sendSms($phoneNumber, "Your verification code is: $verificationCode");

        return response()->json(['phone-message' => 'Verification code sent.'], 400);
    }


    /**
     * Verify code
     *
     * @bodyParam verification_code int required The 4-digit verification code. Example: 1234
     *
     * @authenticated
     *
     * @response 200 {
     *  "message": "Verification successful!"
     * }
     * @response 400 {
     *  "message": "Invalid verification code. Please try again."
     * }
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|digits:4'
        ]);
    
        $user = User::find(auth()->id());

        $expectedCode = $user->verification_code; 
    
        $inputCode = $request->input('verification_code');
    
        if ($inputCode == $expectedCode) {
            $user = User::find(auth()->id());
            $user->phone_verified_at = Carbon::now();
            $user->save();
            return response()->json(['phone-message' => 'Verification successful!'], 400);
        } else {
            return response()->json(['phone-message' => 'Invalid verification code. Please try again.'], 400);
        }
    }
}
