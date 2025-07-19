<?php

namespace App\Http\Controllers\Api\v1;


use App\Models\User;
use App\Traits\Kyctrait;
use App\Models\KycDetail;
use App\Traits\ApiLogger;
use Illuminate\Http\Request;
use App\Traits\Cibilexperian;
use App\Models\Usersrejection;
use Illuminate\Support\Carbon;
use App\Models\Deviceinformation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;

class KycController extends BaseController
{
    use Cibilexperian,Kyctrait,ApiLogger;
    public function requestKyc(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_identifier' => 'required',
                'userid' => 'required|integer|exists:users,id',
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed', $validator->errors(), 422);
            }

            // Log device info if present
            if ($request->filled('deviceId')) {
                $deviceInfo = Deviceinformation::create([
                    'deviceid'      => $request->deviceId,
                    'user_id'       => $request->userid,
                    'lat'           => $request->lat,
                    'lon'           => $request->lon,
                    'address'       => $request->address,
                    'state'         => $request->state,
                    'city'          => $request->city,
                    'pincode'       => $request->pincode,
                    'firebasetoken' => $request->firebaseToken,
                    'appversion'    => $request->version,
                    'event'         => "KYC Initiate",
                ]);
                $this->logApi($request, $deviceInfo);
            }

            // Update user email
            $user = User::findOrFail($request->userid);
            $user->update([
                'email' => $request->email,
            ]);

            // Check KYC expiration
            $kyc = KycDetail::where('user_id', $user->id)->first();
            $isExpired = $kyc && $kyc->capture_expires_at < now();

            // Generate KYC request if expired or not existing
            if ($isExpired || !$kyc) {
                $response = $this->generaterequest($request->customer_identifier, $user->id);

                if (isset($response['error'])) {
                    return $this->sendError('Something went wrong with KYC', $response['message'] ?? 'Unknown error', 500);
                }

                return $this->sendSuccess([
                    'customer_identifier' => $request->customer_identifier,
                    'kid'                 => $response['id'] ?? null,
                    'access_token'        => $response['access_token']['id'] ?? null,
                ], 'Token Generated Successfully!');
            }

            // If not expired and exists, return last saved data
            return $this->sendSuccess([
                'customer_identifier'  => $request->customer_identifier,
                'kid'                  => $kyc->kid ?? null,
                'access_token'         => $kyc->token ?? null,
                'user_journey_status'  => $user->user_journey_status ?? null,
            ], 'Token Already Exists');

        } catch (\Exception $e) {
            \Log::error('KYC request error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->sendError('Internal server error', $e->getMessage(), 500);
        }
    }

   public function responseKyc(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'kid' => 'required',
            'userid' => 'required|integer',
            'response_code' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }
        $user=User::where('id',$request->userid)->first();
        if(!$user){
             return $this->sendSuccess([
                'user_journey_status' => null,
                'customerdetails' => null,
             ],'Limit Assigned!'
            );
        }

        if($user->user_journey_status=='8'){
            return $this->cibilrefetch($user->id);
        }
        try {
            // Find the user by ID
            $user = User::findOrFail($request->userid);

            // Handle the response based on the response code
            switch ($request->response_code) {
                case 1001: // Success code
                    return $this->handleSuccessResponse($request, $user);
                
                case 1002: // KYC Failed
                    return $this->standardResponse(0, 'KYC Failed!', $user);

                case -1000: // KYC Cancelled
                    return $this->standardResponse(0, 'KYC Cancelled By User!', $user);

                default: // Not found or unexpected response code
                    return $this->standardResponse(0, 'Not Found!', $user, null, 404);
            }
        } catch (\Exception $e) {
            // Handle unexpected errors
            return $this->sendError('An error occurred.', $e->getMessage(), 500);
        }
        

    }




    private function cibilrefetch($userid)
    {
        $user=User::where('id',$userid)->first();
        $cibilData = $this->requestcibildata($userid);

        if($cibilData==0){
            $user->user_journey_status = '100'; //cibil REJECTED
            $user->update();
            Usersrejection::create([
                'user_id'=>$userid,
                'reason'=>'Cibil Not Found!'
            ]);

            return $this->standardResponse(1, 'Unable to Fetch Cibil Score!', $user, 0, 200);    
        }

        // Check CIBIL score criteria
        if ($cibilData['cibilscore'] < 720) {
            
            $user->user_journey_status = '104'; //DISABLE USER TO CHECK CIBIL FOR ATLEAST 3 MONTHS
            $user->save(); 

            Usersrejection::create([
                'user_id'=>$userid,
                'reason'=>'Cibil Less then 720!'
            ]);

            return $this->standardResponse(1, 'CIBIL Score is Less than our Criteria!', $user, 0, 200);
        }

        $user->user_journey_status = '4'; //if cibil successfully fetched 
        $user->update();

        // Call bureau data
        $bureauData = $this->requestbureaudata($cibilData['cibildata'], $cibilData['reportnumber'],$userid);

        if($bureauData==0){

            $user->user_journey_status = '101'; //bureau failed
            $user->update();

            Usersrejection::create([
                'userid'=>$userid,
                'reason'=>'Failed to fetch Bureau data!'
            ]);

            return $this->standardResponse(1, 'Unable to Fetch Bureau data!', $user, 0, 200);
        }


    }

    
    private function handleSuccessResponse(Request $request, $user)
    {
 

        $kycResponseData = $this->getResponse($request->kid, $request->userid);
        //print_r(json_encode($kycResponseData));die;

        if ($kycResponseData['status'] === 'success') {
            
            $user->user_journey_status = '3';
            $user->save();

        } elseif ($kycResponseData['status'] === 'error') {
            
            return $this->standardResponse(0, 'The Aadhaar photo and selfie do not match. Please contact the support team for assistance. ', $user, null, 200);
           
        }elseif ($kycResponseData['status'] === 'kycdocerror') {
            
            return $this->standardResponse(0, 'Aadhar & Pan Card Details already exists!', $user, null, 200);
           
        }  elseif ($kycResponseData['status'] === 'exception') {
            
            return $this->standardResponse(0, $kycResponseData['message'], $user, null, 200);
            
        }else{
            return $this->standardResponse(0, 'Aadhar & Pan Card Details already exists OR Error in Processing KYC Data, Please contact Support!', $user, null, 200);

        }

        


        // Fetch CIBIL data
        $cibilData = $this->requestcibildata($request->userid);
        //print_r($cibilData);die;

        if($cibilData==0){

            $user->user_journey_status = '100'; //cibil REJECTED
            $user->update();

            Usersrejection::create([
                'userid'=>$request->userid,
                'reason'=>'Cibil Not Found!'
            ]);


            return $this->standardResponse(1, 'Unable to Fetch Cibil Score!', $user, 0, 200);    
        }
       
        // Check CIBIL score criteria
        if ($cibilData['cibilscore'] < 720) {
            
            $user->user_journey_status = '104'; //DISABLE USER TO CHECK CIBIL FOR ATLEAST 3 MONTHS
            $user->save(); 

            Usersrejection::create([
                'userid'=>$request->userid,
                'reason'=>'Cibil Less then 720!'
            ]);

            return $this->standardResponse(1, 'CIBIL Score is Less than our Criteria!', $user, 0, 200);
        }

        $user->user_journey_status = '4'; //if cibil successfully fetched 
        $user->update();


        // Call bureau data
        $bureauData = $this->requestbureaudata($cibilData['cibildata'], $cibilData['reportnumber'],$request->userid);
       

        if($bureauData==0){

            $user->user_journey_status = '101'; //bureau failed
            $user->update();

            Usersrejection::create([
                'userid'=>$request->userid,
                'reason'=>'Failed to fetch Bureau data!'
            ]);

            return $this->standardResponse(1, 'Unable to Fetch Bureau data!', $user, 0, 200);
        }


        // successfull Bureau Fetched 
        $user->user_journey_status = '5';
        $user->update();
    
        //call hardchecks for bureau
        $HardcheckTraitdata=$this->evaluateCredit($request->userid);
        //return $HardcheckTraitdata;

        if($HardcheckTraitdata==0){

            $user->user_journey_status = '102'; //bureau failed
            $user->update();

            Usersrejection::create([
                'userid'=>$request->userid,
                'reason'=>'Failed in Bureau and Device Connect, Limit Not Assigned'
            ]);

            return $this->standardResponse(1, 'Unable Assign Limit !', $user, 0, 200);
        }
                

        $user->user_journey_status = '7'; //limit allocated
        $user->save();

        $userData = User::with([
            'aadharData:id,userid,fullname,current_address,selfie,dob,current_state,current_city', // Include primary and foreign keys
            //'walletlimit:id,userid,emi_limit,bnpl_limit,emi_card_number,bnpl_card_number' // Include primary and foreign keys
        ])->find($request->userid);
        
        return response()->json([
            'status' => 1,
            'message' => 'Limit Assigned!',
            'user_journey_status' => $user->user_journey_status,
            'customerdetails' => $userData
        ]);
    }

    private function standardResponse($status, $message, $user, $limit = null, $code = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'limit' => $limit,
            'user_journey_status' => $user->user_journey_status,
            'customerdetails' => $user
        ], $code);
    }


}
