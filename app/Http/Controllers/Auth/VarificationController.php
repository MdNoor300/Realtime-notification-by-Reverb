<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\OtpRequest;
use App\Http\Services\Common\VerificationService;
use App\Models\Otp;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class VarificationController extends Controller
{
    private $verificationService;
    public function __construct(VerificationService $verificationService){
        $this->verificationService = $verificationService;
    }
    // Send OTP
    public function sendOtp(OtpRequest $request)
    {
        $otp = randomNumber(4);
        do {
            $otp = randomNumber(4);
            $exists_otp = Otp::where(['otp' => $otp])->exists();
        } while ($exists_otp);

        $token_data = [
            'otp' => $otp,
            'type' => $request->action,
            'expired_at' => Carbon::now()->addMinutes(3),
            'email' =>  $request->email
        ];

        try {
            Otp::updateOrCreate(['email' => $request->email], $token_data);
            $data = [
                'email' => $request->email,
                'expired_at' => $token_data['expired_at'],
                'otp'    => $otp,
            ];
           // Send Email
            $sideData = Setting::first();
            sendOtp($request->email,$sideData, $otp);
            return successResponse(__("OTP Have to hide, remember"), $data);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $otp = Otp::where(['otp' => $request->otp, 'email' => $request->email])->first();
        if (empty($otp)) {
            return errorResponse(__('Did not match', ['key' => __('OTP')]));
        }
        if (Carbon::now() > Carbon::parse($otp->expired_at)) {
            return errorResponse(__('OTP has been expired'));
        }
        if($otp->type != $request->action){
            return errorResponse(__('Invalid action'));
        }
        $otp->delete();

        $payload = array(
           "email" => $request->email
        );

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
        return successResponse(__('OTP verified successfully'),['token' => $token]);
    }
}
