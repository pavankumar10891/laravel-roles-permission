<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use App\Traits\ApiLogger;
use Illuminate\Http\Request;
use App\Traits\Smsnotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;


class AuthApiController extends BaseController
{
    use Smsnotification, ApiLogger;

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $otp = rand(1111, 9999);
        $message = 'Your otp is: ' . $otp . ' for MSPLON valid only ';
        $dltContentId = '1707173208993184236';
        $result = $this->sendSmsNotification($request->phone, $request->text, $dltContentId);

        if (isset($result['error'])) {
            return $this->sendError('SMS sending failed', $result);
        }

        return $this->sendSuccess($result, 'SMS sent successfully');

    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|digits:10',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed', $validator->errors(), 422);
            }

            $otp = rand(100000, 999999);
            $otpExpiresAt = now()->addMinutes(10);

            $user = User::where('mobile', $request->mobile)->first();

            if ($user) {
                $user->update([
                    'otp' => $otp,
                    'otp_expires_at' => $otpExpiresAt,
                    'device_id' => $request->deviceId ?? $user->device_id,
                ]);
            } else {
                $user = User::create([
                    'name' => 'dummy',
                    'email' => 'dummy' . time() . '@gmail.com',
                    'mobile' => $request->mobile,
                    'password' => Hash::make($request->mobile),
                    'otp' => $otp,
                    'is_verified' => false,
                    'device_id' => $request->deviceId,
                    'otp_expires_at' => $otpExpiresAt,
                ]);
            }

            $smsResult = $this->sendSmsNotification(
                $request->mobile,
                $otp.' is your OTP for Mobile No Verify. OTP is confidential. Please do not share this with anyone for security reasons-Team MELTRON',
                '1707173208993184236'
            );

            $this->logApi($request, $smsResult);

            if (isset($smsResult['error'])) {
                return $this->sendError('SMS sending failed', [], 500);
            }

            return $this->sendSuccess(['otp_sent_to' => $request->mobile], 'OTP sent successfully');

        } catch (\Throwable $e) {
            \Log::error('Registration OTP Error: ' . $e->getMessage(), [
                'mobile' => $request->mobile,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Something went wrong while sending OTP', [], 500);
        }
    }



    public function verifyOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'mobile' => 'required|digits:10',
                'otp'    => 'required|digits:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $user = User::where('mobile', $request->mobile)->first();

            if (!$user || $user->otp !== $request->otp) {
                return $this->sendError('Invalid OTP', [], 401);
            }

            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['error' => 'OTP expired'], 410);
            }

            $user->update([
                'is_verified'     => true,
                'otp'             => null,
                'otp_expires_at'  => null,
                'last_login_at'   => now(),
                'device_id'       => $request->deviceId,
                'firebase_token'  => $request->firebaseToken,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            $result = [
                'token'   => $token,
                'userid'  => $user->id,
            ];

            $this->logApi($request, $result);

            return $this->sendSuccess($result, 'OTP verified successfully.');

        } catch (\Throwable $e) {
            \Log::error('OTP Verification Failed: ' . $e->getMessage(), [
                'mobile' => $request->mobile ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Something went wrong during OTP verification.', [], 500);
        }
    }




    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendSuccess([], 'Logged out successfully');
    }

    public function updateDeviceId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'userid'    => 'required|exists:users,id',
                'deviceId'  => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $user = User::find($request->userid);

            if (!$user) {
                return $this->sendError('User not found', [], 404);
            }

            $user->device_id = $request->deviceId;
            $user->save();

            return $this->sendSuccess([], 'Device ID saved successfully');

        } catch (\Throwable $e) {
            \Log::error('Device ID Update Error: ' . $e->getMessage(), [
                'user_id' => $request->userid ?? null,
                'trace'   => $e->getTraceAsString(),
            ]);

            return $this->sendError('Something went wrong while updating device ID', [], 500);
        }
    }


    public function getUserDetailById(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'userid' => 'required|integer|exists:users,id',
            ]);

           if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $user = User::with('aadharData')->find($request->userid);

            if (!$user) {
                return $this->sendError('User not found', [], 404);
            }

            $res = [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'mobile'       => $user->mobile,
                'device_id'    => $user->device_id,
                'is_verified'  => $user->is_verified,
                'user_journey_status'  => $user->user_journey_status,
                'user_journey_status_post'  => $user->user_journey_status_post,
                'user_assigned_date'  => $user->user_assigned_date,
                'block_reason'  => $user->block_reason,
                'created_at'   => $user->created_at->toDateTimeString(),
                'aadhar'     => $user->aadharData ? [
                'uid'               => $user->aadharData->uid,
                'fullname'          => $user->aadharData->fullname,
                'gender'            => $user->aadharData->gender,
                'dob'               => $user->aadharData->dob,
                ] : null
            ];

            return $this->sendSuccess($res, 'User details fetched successfully');

        } catch (\Throwable $e) {
            \Log::error('Get User Detail Error: ' . $e->getMessage(), [
                'userid' => $request->userid ?? null,
                'trace'  => $e->getTraceAsString(),
            ]);

            return $this->sendError('Something went wrong while fetching user details', [], 500);
        }
    }

}
