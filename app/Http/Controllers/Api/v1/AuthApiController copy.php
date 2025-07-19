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
        echo "Hii";die;
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
            //'device_id' => 'nullable|string',
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
                'device_id' => $request->deviceId ?? $user->deviceId,
            ]);
        } else {
            $user = User::create([
                'name' => 'dummy',
                'email' => 'dummy' . time() . '@gmail.com',
                'mobile' => $request->mobile,
                'password' => Hash::make($request->mobile),
                'otp' => $otp,
                'is_verified' => false,
                'device_id' => $request->device_id,
                'otp_expires_at' => $otpExpiresAt,
            ]);
        }
        echo $otp;die;
        $smsResult = $this->sendSmsNotification($request->mobile,$otp);

        $this->logApi($request, $smsResult);

        if (isset($smsResult['error'])) {
            return $this->sendError('SMS sending failed', $smsResult);
        }

        return $this->sendSuccess(['otp_sent_to' => $request->mobile], 'OTP sent successfully');
    }


    public function verifyOtp(Request $request)
    {
        $user = User::where('mobile', $request->mobile)->first();
        //echo $user->otp;die;

        if (!$user || $user->otp !== $request->otp) {
            return $this->sendError('SMS sending failed','Invalid OTP.',401);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['error' => 'OTP expired'], 410);
        }

        $user->is_verified = true;
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->last_login_at = now();
        $user->device_id = $request->deviceId;
        $user->firebase_token = $request->firebaseToken;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;
        $result = [
            'token'   => $token,
            'userid'    => $user->id
        ];
        $this->logApi($request, $result);
        return $this->sendSuccess($result, 'OTP verified successfully.');
    }



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendSuccess([], 'Logged out successfully');
    }

    public function updateDeviceId(Request $request)
    {
        $request->validate([
            'userid' => 'required|exists:users,id',
            'deviceId' => 'required|string|max:255',
        ]);

        $user = User::find($request->userid);

        if (!$user) {
            return $this->sendError('User not found', 'Invalid User',404);
        }

        $user->device_id = $request->deviceId;
        $user->save();

        return response()->json([
            'status' => '1',
            'message' => 'Device ID saved successfully'
        ]);
        return $this->sendSuccess([], 'Device ID saved successfully');
    }

    public function getUserDetailById(Request $request)
    {
        $request->validate([
            'userid' => 'required|exists:users,id',
        ]);

        $user = User::with('aadhar')->find($request->userid);

        if (!$user) {
            return $this->sendError('User not found', 'Invalid User',404);
        }

        $res =  [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'mobile'     => $user->mobile,
                'device_id'  => $user->device_id,
                'is_verified'=> $user->is_verified,
                'created_at' => $user->created_at->toDateTimeString(),
                'aadhar'     => $user->aadhar ? [
                'uid'               => $user->aadhar->uid,
                'fullname'          => $user->aadhar->fullname,
                'gender'            => $user->aadhar->gender,
                'dob'               => $user->aadhar->dob,
                ] : null
        ];
        return $this->sendSuccess($res, 'User details fetched successfully');
    }


    public function updateMobile(Request $request)
    {
        try {
            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'new_mobile' => 'required|digits:10|unique:users,mobile',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            // Optional: Send OTP here for verification before updating
            // $otp = rand(100000, 999999);
            // $user->otp = $otp;
            // $user->otp_expires_at = now()->addMinutes(10);
            // $user->save();
            //     $smsResult = $this->sendSmsNotification(
            //     $request->new_mobile,
            //     "Your OTP is $otp. It is valid for 10 minutes.",
            //     '1707173208993184236'
            // );

            // Update mobile
            $user->mobile = $request->new_mobile;
            $user->save();

            return $this->sendSuccess([], 'Mobile number updated successfully.');

        } catch (\Throwable $e) {
            \Log::error('Update Mobile Error: ' . $e->getMessage());
            return $this->sendError('Something went wrong. Please try again.', [], 500);
        }
    }




}
