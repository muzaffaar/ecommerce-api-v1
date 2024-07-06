<?php


use App\Http\Controllers\Auth\PhoneVerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Auth::routes(); 

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Route::get('/phone-verification', [PhoneVerificationController::class, 'sendVerificationCode'])->name('phone.verification.notice');
// Route::post('/phone-verification', [PhoneVerificationController::class, 'resendVerificationCode'])->middleware(['auth'])->name('verification.resend');
// Route::post('/phone-verify', [PhoneVerificationController::class, 'verifyCode'])->middleware(['auth'])->name('phone.verify');