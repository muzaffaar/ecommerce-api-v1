<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TwilioService;
use Illuminate\Support\Carbon;

class PhoneVerificationController extends Controller
{
    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
        $this->middleware(['auth']);
    }

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
        return view('auth.verify_phone')->with('message', 'Verification code has been sent, please check you messanger.');
        
    }
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

        return redirect()->back()->with('message', 'Verification code sent.');
    }

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
            return redirect()->route('home')->with('message', 'Verification successful!');
        } else {
            return redirect()->back()->with('message', 'Invalid verification code. Please try again.');
        }
    }
}
