<?php

namespace App\Http\Services\Auth;

use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class VerificationService
{
    public function sendOtp($request)
    {
         // Otp create
         return successResponse(__("OTP Have to hide, remember"));
    }

    public function verify_otp($request){
        $otp = Otp::where(['otp' => $request->otp, 'email' => $request->email])->first();
        if (empty($otp)) {
            return errorResponse(__('not_matched', ['key' => __('OTP')]));
        }
        if (Carbon::now() > Carbon::parse($otp->expired_at)) {
            return errorResponse(__('OTP has been expired'));
        }
        $otp->delete();
        $user = User::where(['email' => $request->email])->first();
        $token = $user->createToken($user->uuid . 'user')->accessToken;
        return successResponse(__('OTP verified successfully'), ['token' => $token]);
    }

}
