<?php
namespace App\Traits;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Kyclog;
use App\Models\PanData;
use App\Models\KycDetail;
use App\Models\AAdharData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

trait Kyctrait {

    public function generateRequest($customerIdentifier, $userId)
    {
        //$url = 'https://api.digio.in/client/kyc/v2/request/with_template'; production
        $url = 'https://ext.digio.in:444/client/kyc/v2/request/with_template';
        $auth = $this->getAuthenticationString();

        $payload = [
            'customer_identifier'     => $customerIdentifier,
            'template_name'           => 'DIGILOCKER_CONDITIONAL_SELFIE',
            'notify_customer'         => false,
            'generate_access_token'   => true,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Basic {$auth}",
                'Content-Type'  => 'application/json',
            ])->post($url, $payload);

            if (!$response->successful()) {
                Log::error('KYC API error', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'error'   => true,
                    'status'  => $response->status(),
                    'message' => $response->body()
                ];
            }

            $responseData = $response->json();
            $expiresAt = Carbon::now()->addDays(10);

            // Save KYC details
            KycDetail::updateOrCreate(
                ['user_id' => $userId],
                [
                    'capture_expires_at' => $expiresAt,
                    'kid'                => $responseData['id'] ?? null,
                    'token'              => $responseData['access_token']['id'] ?? null,
                ]
            );

            // Log raw KYC response
            Kyclog::updateOrCreate(
                ['user_id' => $userId],
                ['request' => json_encode($responseData)]
            );

            // Update user status
            $user = User::find($userId);
            if ($user) {
                $user->user_journey_status = '2';
                $user->save();
            }

            // Append journey status to response
            $responseData['user_journey_status'] = '2';

            return $responseData;

        } catch (\Exception $e) {
            Log::error('KYC request failed', ['exception' => $e]);
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }

    function getResponse($request_id, $userid)
    {
        // Use the appropriate URL, either live or sandbox
        $url = 'https://ext.digio.in:444/client/kyc/v2/' . $request_id . '/response'; // live

        // Get your basic authentication string
        $auth = $this->getauthenticationstring();

        try {
            $maxRetries = 3; // Set maximum retries
            $retryDelay = 10; // Retry delay in seconds

            // Retry loop
            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                // Make the request with authorization headers
                $response = Http::withHeaders([
                    'Authorization' => "Basic {$auth}",
                    'Content-Type' => 'application/json',
                ])->post($url);

                // If the request was successful
                if ($response->successful()) {
                    $responseData = $response->json();
                      //echo "<pre>";print_r($responseData);die;
                    $kyclogs = Kyclog::updateOrCreate(
                        ['user_id' => $userid],
                        [
                            'user_id' => $userid,
                            'response' => json_encode($responseData),
                         
                        ]
                    );
                    
                    // Check for the 'approved' status
                     //if (isset($responseData['status']) && ($responseData['status'] === 'approved' || $responseData['status'] === 'approval_pending')) {

                         
                    if (isset($responseData['status']) && $responseData['status'] === 'approved') {

                   
                        // Call the processApprovedResponse function and return success
                        $processdata=$this->processApprovedResponse($responseData, $userid);

                        return $processdata;

                        if($processdata['status']==='success'){

                            return [
                            'status' => 'success',
                            'message' => 'KYC approved and processed successfully.',
                            'data' => $responseData
                            ];
                        }else{

                            return [
                            'status' => 'kycdocerror',
                            'message' => 'Unable to process KYC!',
                            'data' =>null
                            ];
                        }

                        
                    } elseif (isset($responseData['status']) && $responseData['status'] === 'approval_pending') {
                        // If status is 'approval_pending', retry after a delay
                        sleep($retryDelay);
                    } else {
                        // Handle other unexpected statuses
                        return [
                            'status' => 'error',
                            'message' => 'Unexpected status received: ' . $responseData['status'],
                            'data' => $responseData
                        ];
                    }
                } else {
                    // If the request failed, return error
                    return [
                        'status' => 'error',
                        'message' => 'API Request failed with status: ' . $response->status(),
                        'response_body' => $response->body()
                    ];
                }
            }

            // If all retries are exhausted
            return [
                'status' => 'error',
                'message' => 'Max retries reached, no approved response received.'
            ];

        } catch (\Exception $e) {
            // Return exception details
            return [
                'status' => 'exception',
                'message' => $e->getMessage()
            ];
        }
    }

    function processApprovedResponse($responseData, $userid)
    {
        try {
            $aadhaarDetails = $responseData['actions'][0]['details']['aadhaar'];
            $panDetails = isset($responseData['actions'][0]['details']['pan']) ? $responseData['actions'][0]['details']['pan'] : null;



            if($panDetails==null){
                $checkForUidAndPan = $this->check_adhar_and_pan($aadhaarDetails['id_number'], null, $userid,$aadhaarDetails['name']);
            }else{
                $checkForUidAndPan = $this->check_adhar_and_pan($aadhaarDetails['id_number'], $panDetails['id_number'], $userid,$aadhaarDetails['name']);
            }

            // Check if Aadhaar and PAN are valid and process them
            
            //return $checkForUidAndPan;

            if ($checkForUidAndPan['status'] ==='success') 
            {


                // If valid, download KYC files
                $profilePic = $this->getKycfiles($responseData['actions'][2]['file_id'], $userid, 'profilepic.jpg');
                $aadhar = $this->getKycfilesdoc($responseData['actions'][0]['execution_request_id'], $userid, 'aadhar.pdf', 'AADHAAR');
                $pan = $this->getKycfilesdoc($responseData['actions'][0]['execution_request_id'], $userid, 'pan.pdf', 'PAN');

                    // Update or create Aadhaar and PAN data in the database
                    AAdharData::updateOrCreate(
                    ['user_id' => $userid],
                    [
                        'user_id' => $userid,
                        'uid' => $aadhaarDetails['id_number'],
                        'fullname' => $aadhaarDetails['name'],
                        'gender' => $aadhaarDetails['gender'],
                        'dob' => Carbon::createFromFormat('d/m/Y', $aadhaarDetails['dob'])->format('Y-m-d'),
                        'father_name' => isset($aadhaarDetails['father_name']) && !empty($aadhaarDetails['father_name']) 
                     ? $aadhaarDetails['father_name'] 
                     : $aadhaarDetails['name'],
                        'current_address' => $aadhaarDetails['current_address_details']['address'],
                        'current_post_office' => $aadhaarDetails['current_address_details']['locality_or_post_office'],
                        'current_city' => $aadhaarDetails['current_address_details']['district_or_city'],
                        'current_state' => $aadhaarDetails['current_address_details']['state'],
                        'current_pincode' => $aadhaarDetails['current_address_details']['pincode'],
                        'permanent_address' => $aadhaarDetails['permanent_address_details']['address'],
                        'permanent_post_office' => $aadhaarDetails['permanent_address_details']['locality_or_post_office'],
                        'permanent_city' => $aadhaarDetails['permanent_address_details']['district_or_city'],
                        'permanent_state' => $aadhaarDetails['permanent_address_details']['state'],
                        'permanent_pincode' => $aadhaarDetails['permanent_address_details']['pincode'],
                        'selfie' =>'https://app-backend-images.s3.ap-south-1.amazonaws.com/app-customers/'.$userid.'/kyc-documents/profilepic.jpg',
                        'aadharcard_image' =>'https://app-backend-images.s3.ap-south-1.amazonaws.com/app-customers/'.$userid.'/kyc-documents/aadhar.pdf'

                    ]);


                    // Check if PAN is already provided
                    if (isset($responseData['actions'])) {
                         $this->storePanDetails($responseData['actions'], $userid);
                    }
                    // Check for uploaded PAN via image
                    if (isset($responseData['actions'])) {
                        $this->storeUploadedPanDetails($responseData['actions'], $userid);
                    }


                // Return success message
                return [
                    'status' => 'success',
                    'message' => 'KYC files processed and data updated successfully.'
                ];
            }else{

                // Return success message
                return [
                    'status' => 0,
                    'message' => 'Pan Card and Aadhar Data already exists!'
                ];

            }

            return [
                'status' => 0,
                'message' => 'Aadhaar or PAN data already exists.'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'exception',
                'message' => $e->getMessage()
            ];
        }
    }

    function check_adhar_and_pan($uid, $panid, $userid,$aadharname)
    {
        // Check if Aadhaar data already exists for the given UID but belongs to another user
        $aadhaarExists = AAdharData::where('uid', $uid)
        ->where('fullname',$aadharname)
        ->where('user_id', '!=', $userid)->exists();

        // Check if PAN data already exists for the given PAN but belongs to another user
        if($panid!=null){
            $panExists = PanData::where('panid', $panid)->where('userid', '!=', $userid)->exists();
        }else{
            $panExists=null;
        }
                

        if ($aadhaarExists && $panExists) {
            // If both Aadhaar and PAN data exist and belong to another user, return an error message
            return [
                'status' => 'error',
                'message' => 'Aadhaar and PAN already exist for another user.',
                'aadhaar_exists' => true,
                'pan_exists' => true
            ];
        } elseif ($aadhaarExists) {
            // If Aadhaar exists for another user, return an Aadhaar error
            return [
                'status' => 'error',
                'message' => 'Aadhaar already exists for another user.',
                'aadhaar_exists' => true,
                'pan_exists' => false
            ];
        } elseif ($panExists) {
            // If PAN exists for another user, return a PAN error
            return [
                'status' => 'error',
                'message' => 'PAN already exists for another user.',
                'aadhaar_exists' => false,
                'pan_exists' => true
            ];
        } else {
            // If neither Aadhaar nor PAN exist, return success
            return [
                'status' => 'success',
                'message' => 'Aadhaar and PAN are available for use.',
                'aadhaar_exists' => false,
                'pan_exists' => false
            ];
        }
    }

    function storePanDetails(array $jsonData, int $userId): ?Pandata
    {
        // Check if the structure is as expected
        if (isset($jsonData[0]['details']) && !empty($jsonData[0]['details'])) {
            $panData = $jsonData[0]['details']['pan'] ?? null;

            // Validate that PAN data exists
            if ($panData) {
                // Safely access and assign the PAN data
                $panIdNumber = $panData['id_number'] ?? null;
                $panName = $panData['name'] ?? null;
                $panDob = $panData['dob'] ?? null;
                $panGender = $panData['gender'] ?? null;

                // Validate necessary fields
                if (empty($panIdNumber) || empty($panName) || empty($panDob) || empty($panGender)) {
                    // Log or handle missing data appropriately
                    return null; // or throw an exception if this is critical
                }

                try {
                    // Save to the database (using Laravel's Eloquent)
                    return PanData::updateOrCreate(
                        ['user_id' => $userId],
                        [
                            'user_id' => $userId,
                            'panid' => $panIdNumber,
                            'name' => $panName,
                            'gender' => $panGender,
                            'dob' => Carbon::createFromFormat('d/m/Y', $panDob)->format('Y-m-d'), // Handle potential exceptions here
                            'pancard_image' => 'https://app-backend-images.s3.ap-south-1.amazonaws.com/app-customers/' . $userId . '/kyc-documents/pan.pdf'
                        ]
                    );
                } catch (\Exception $e) {
                    // Log the exception or handle it as needed
                    return null; // or rethrow as needed
                }
            }
        }

        return null; // Return null if no valid data was processed
    }

    function getKycfiles($file_id, $userid, $Kycfilename)
    {
        
        $url = 'https://ext.digio.in:444/client/kyc/v2/media/' . $file_id; //sandbox
        //$url = 'https://api.digio.in/client/kyc/v2/media/' . $file_id; //live
        $auth = $this->getauthenticationstring();

        try {

            $response = Http::withHeaders([
                'Authorization' => "Basic {$auth}",
                'Content-Type' => 'application/json'
            ])->get($url);

            if ($response->successful()) {

                // Define the path where you want to save the file in S3 s3://app-backend-images/kyc-documents/
                $filePath = "app-customers/" . $userid . "/kyc-documents/" . $Kycfilename;

                // Get the response content
                $fileContent = $response->body();

                // Save the file to the S3 bucket
                //Storage::disk('s3')->put($filePath, $fileContent);

                //local storage
                Storage::put($filePath, $fileContent);

                // Get the URL of the stored file on S3
                //$fileUrl = Storage::disk('s3')->url($filePath);

                //local url
                 $filePath = asset("storage/{$filePath}");


                return [
                    'success' => true,
                    'message' => "File has been saved to: " . $filePath
                ];
            } else {
                return [
                    'error' => $response->status(),
                    'message' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }

    function getKycfilesdoc($file_id, $userid, $Kycfilename,$doctype)
    {
        
        $url = 'https://ext.digio.in:444/client/kyc/v2/media/' . $file_id; //sandbox
        //$url = 'https://api.digio.in/client/kyc/v2/media/' . $file_id; //live
        $auth = $this->getauthenticationstring();

        try {

            $response = Http::withHeaders([
                'Authorization' => "Basic {$auth}",
                'Content-Type' => 'application/json'
            ])->get($url, [
                'doc_type' =>$doctype,
                'xml' => 'false'
            ]);

            if ($response->successful()) {

                // Define the path where you want to save the file in S3 s3://app-backend-images/kyc-documents/
                $filePath = "app-customers/" . $userid . "/kyc-documents/" . $Kycfilename;

                // Get the response content
                $fileContent = $response->body();

                // Save the file to the S3 bucket
                //Storage::disk('s3')->put($filePath, $fileContent);

                // local storage
                Storage::put($filePath, $fileContent);

                // Get the URL of the stored file on S3
                //$fileUrl = Storage::disk('s3')->url($filePath);

                //local path
                $filePath = asset("storage/{$filePath}");


                return [
                    'success' => true,
                    'message' => "File has been saved to: " . $filePath
                ];
            } else {
                return [
                    'error' => $response->status(),
                    'message' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }

    function storeUploadedPanDetails(array $jsonData, int $userId)
    {
        // Loop through actions to find relevant PAN details
        foreach ($jsonData as $action) {
            // Check if the action type is "image" and the status is "approved"
            if ($action['type'] === 'image' && $action['status'] === 'approved') {
                $ocrResult = $action['ocr_result'] ?? null;
                $faceMatchResult = $action['face_match_result'] ?? null;

                if ($ocrResult) {
                    // Extract relevant data from OCR results and face match results
                    $panIdNumber = $ocrResult['id_no'] ?? null;
                    $panName = $ocrResult['name'] ?? null;
                    $panDob = $ocrResult['dob'] ?? null;
                    $panGender = 'M';//$ocrResult['fathers_name'] ? 'M' : 'F'; // Assume based on the father's name as a placeholder
                    $confidence = $faceMatchResult['confidence'] ?? null;

                    // Check if all required data is present
                    if ($panIdNumber && $panName && $panDob ) {
                        try {
                            // Save PAN details and face match results to the database
                            PanData::updateOrCreate(
                                ['user_id' => $userId],
                                [
                                    'user_id' => $userId,
                                    'panid' => $panIdNumber,
                                    'name' => $panName,
                                    'gender' => $panGender,
                                    'dob' => Carbon::createFromFormat('d/m/Y', $panDob)->format('Y-m-d'),
                                    'pancard_image' => 'https://app-backend-images.s3.ap-south-1.amazonaws.com/app-customers/' . $userId . '/kyc-documents/pan.pdf',
                                    
                                ]
                            );
                        } catch (\Exception $e) {
                            // Handle any exceptions (log or display an error)
                           // \Log::error('Failed to store PAN details: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }



    private function getAuthenticationString()
    {
        $clientId     = config('app.kyc_client_id_sandbox');  //config('app.kyc_client_id');
        $clientSecret = config('app.kyc_client_secret_sandbox'); //config('app.kyc_client_secret');

        return base64_encode("{$clientId}:{$clientSecret}");
    }
}