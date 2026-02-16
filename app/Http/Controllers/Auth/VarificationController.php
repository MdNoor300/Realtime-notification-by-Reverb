<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Services\Auth\VerificationService;
use App\Models\Otp;
use App\Models\Setting;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VarificationController extends Controller
{
    private $verificationService;
    public function __construct(VerificationService $verificationService)
    {
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
            if (!$sideData) {
                return errorResponse('Settings not configured', 400);
            }
            sendOtp($request->email, $sideData, $otp);
            return successResponse(__("OTP Have to hide, remember"), $data);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        // Fetch the latest OTP for the given email
        $otp = Otp::where('email', $request->email)->latest()->first();

        // Validate OTP exists
        if (!$otp || $otp->otp !== (string) $request->otp) {
            return errorResponse(__('OTP did not match'));
        }

        // Check expiry
        if (!$otp->expired_at || now()->gt($otp->expired_at)) {
            return errorResponse(__('OTP has expired'));
        }

        // Validate OTP action/type
        if ($otp->type !== $request->action) {
            return errorResponse(__('Invalid action'));
        }

        try {
            // Use a transaction to ensure OTP is only deleted after token is generated
            $token = null;
            DB::transaction(function () use ($otp, $request, &$token) {
                // Generate JWT token
                $token = JWT::encode(
                    ['email' => $request->email, 'iat' => time()],
                    config('app.jwt_secret'),
                    'HS256'
                );

                // Delete OTP after successful token generation
                $otp->delete();
            });

            return successResponse(__('OTP verified successfully'), ['token' => $token]);
        } catch (\Exception $e) {
            // Rollback will automatically happen if transaction fails
            return errorResponse(__('Internal server error: ') . $e->getMessage());
        }
    }
}
