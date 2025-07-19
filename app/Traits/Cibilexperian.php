<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Customer\{CibilData,CibilDataLog};
use App\Events\CibilFileUploadEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use DateTime;

trait Cibilexperian
{
    public function requestcibildata($userid)
    {


        $userData = User::with(['aadharData', 'panData:id,panid'])->find($userid);


        if($userData)
        {   
            $getage=$this->calculateAge($userData->aadharData->dob);
            if($getage==0)
            {
                return 0;  
            }    


            //testing data
            $firstname=$userData->aadharData->firstname;
            $lastname=$userData->aadharData->lastname;
            $dobWithoutHyphens = str_replace('-', '', $userData->aadharData->dob);
            $dob=$dobWithoutHyphens;
            $pan=$userData->panData->panid;
            $phone=$userData->phone;

            //echo $phone;die;

            $charactersToRemove = ['~', '!', '#', '$', '%', '^', '*', '=', '|', '?', '+'];
            $cleanedAddress = str_replace($charactersToRemove, '',$userData->aadharData->permanent_address);

            $addressline1=$cleanedAddress;
            $addressline2=$cleanedAddress;
            $city=$userData->aadharData->permanent_city;


            $statecode=$this->getStateCode(strtoupper($userData->aadharData->permanent_state));
            //return $statecode;
            $pincode=$userData->aadharData->permanent_pincode;
            $gendercode=$this->getGenderDescription($userData->aadharData->gender);

            
            $postdata='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:cbv2">
                       <soapenv:Header/>
                       <soapenv:Body>
                          <urn:process>
                             <urn:in>
                                <INProfileRequest>
                        <Identification>
                           <XMLUser>cpu2sdal_prod01</XMLUser>
                    <XMLPassword>Qwerty9980Qwerty</XMLPassword>
                        </Identification>
                        <Application>
                            <FTReferenceNumber></FTReferenceNumber>
                            <CustomerReferenceID></CustomerReferenceID>
                            <EnquiryReason>13</EnquiryReason>
                            <FinancePurpose>99</FinancePurpose>
                            <AmountFinanced>5000</AmountFinanced>
                            <DurationOfAgreement>6</DurationOfAgreement>
                            <ScoreFlag>3</ScoreFlag>
                            <PSVFlag>0</PSVFlag>
                        </Application>
                        <Applicant>
                            <Surname>'.$lastname.'</Surname>
                            <FirstName>'.$firstname.'</FirstName>
                            <MiddleName1></MiddleName1>
                            <MiddleName2></MiddleName2>
                            <MiddleName3></MiddleName3>
                            <GenderCode>'.$gendercode.'</GenderCode>
                            <IncomeTaxPAN>'.$pan.'</IncomeTaxPAN>
                            <PANIssueDate></PANIssueDate>
                            <PANExpirationDate></PANExpirationDate>
                            <PassportNumber></PassportNumber>
                            <PassportIssueDate></PassportIssueDate>
                            <PassportExpirationDate></PassportExpirationDate>
                            <VoterIdentityCard></VoterIdentityCard>
                            <VoterIDIssueDate></VoterIDIssueDate>
                            <VoterIDExpirationDate></VoterIDExpirationDate>
                            <DriverLicenseNumber></DriverLicenseNumber>
                            <DriverLicenseIssueDate></DriverLicenseIssueDate>
                            <DriverLicenseExpirationDate></DriverLicenseExpirationDate>
                            <RationCardNumber></RationCardNumber>
                            <RationCardIssueDate></RationCardIssueDate>
                            <RationCardExpirationDate></RationCardExpirationDate>
                            <UniversalIDNumber></UniversalIDNumber>
                            <UniversalIDIssueDate></UniversalIDIssueDate>
                            <UniversalIDExpirationDate></UniversalIDExpirationDate>
                            <DateOfBirth>'.$dob.'</DateOfBirth>
                            <STDPhoneNumber></STDPhoneNumber>
                            <PhoneNumber>'.$phone.'</PhoneNumber>
                            <TelephoneExtension></TelephoneExtension>
                            <TelephoneType></TelephoneType>
                            <MobilePhone></MobilePhone>
                            <EMailId></EMailId>
                        </Applicant>
                        <Details>
                            <Income></Income>
                            <MaritalStatus></MaritalStatus>
                            <EmployStatus></EmployStatus>
                            <TimeWithEmploy></TimeWithEmploy>
                            <NumberOfMajorCreditCardHeld></NumberOfMajorCreditCardHeld>
                        </Details>
                        <Address>
                            <FlatNoPlotNoHouseNo>'.$addressline1.'</FlatNoPlotNoHouseNo>
                            <BldgNoSocietyName></BldgNoSocietyName>
                            <RoadNoNameAreaLocality>'.$addressline2.'</RoadNoNameAreaLocality>
                            <City>'.$city.'</City>
                            <Landmark></Landmark>
                            <State>'.$statecode.'</State>
                            <PinCode>'.$pincode.'</PinCode>
                        </Address>
                        <AdditionalAddressFlag>
                            <Flag>N</Flag>
                        </AdditionalAddressFlag>
                        <AdditionalAddress>
                            <FlatNoPlotNoHouseNo>'.$addressline1.'</FlatNoPlotNoHouseNo>
                            <BldgNoSocietyName></BldgNoSocietyName>
                            <RoadNoNameAreaLocality>'.$addressline2.'</RoadNoNameAreaLocality>
                            <City>'.$city.'</City>
                            <Landmark></Landmark>
                            <State>'.$statecode.'</State>
                            <PinCode>'.$pincode.'</PinCode>
                        </AdditionalAddress>
                    </INProfileRequest>
                    </urn:in>
                          </urn:process>
                       </soapenv:Body>
                    </soapenv:Envelope>
                ';

                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://connect.experian.in:443/nextgen-ind-pds-webservices-cbv2/endpoint',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS =>$postdata,
                  CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/xml',
                    'Cookie: incap_ses_739_2274150=6d6ZeYIcmS16V8pNtnRBCmMiyGYAAAAAsAzZjOkYCvjdAPGdM6zqRA==; nlbi_2274150=sZNUMv72uA0K1u+zqAeeIQAAAAAEHiS8qik/2+g5KLFF8V7/; visid_incap_2274150=4ZRe0WMqTcKa7wlABPsQGmMiyGYAAAAAQUIPAAAAAADDk9qwW8HXEHN5B7tZWRRK'
                  ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                $decoded_response = html_entity_decode($response);

                // Create a new Cibildata record
                /*$cibildata = new Cibildatalog();
                $cibildata->userid = $userid;
                //$cibildata->cibilscore = $bureauScore;
                $cibildata->cibilrequestlink =$postdata;
                $cibildata->cibilresponselink = json_encode($decoded_response); // Corrected line
                $cibildata->save();*/


                $cleaned_response = str_replace(['</ns2:out>', '</SOAP-ENV:Body>', '</SOAP-ENV:Envelope>','</ns2:processResponse>'], '', $decoded_response);

                $start_pos = strpos($cleaned_response, '<?xml');
                $xml_content = substr($cleaned_response, $start_pos);
                $xml_object = simplexml_load_string($xml_content);

                
                $json_result = json_encode($xml_object, JSON_PRETTY_PRINT);

                // Decode the JSON string to an array
                $jsonArray = json_decode($json_result, true);

                // Replace empty objects with "None"
                $updatedJsonArray = $this->replaceEmptyObjects($jsonArray);

                // Encode the updated array back to JSON
                $updatedJsonString = json_encode($updatedJsonArray, JSON_PRETTY_PRINT);

          


                $bureaupayload='
                {
                    "data":{
                        "jsonExperianReport":
                            '.$updatedJsonString.'
                        
                    }
                }
                ';

                

                //print_r($bureaupayload);die;
                //testing
               

                if(!empty($json_result))
                {

                    $now = Carbon::now();
                    $formattedDateTime = $now->format('d-m-Y_H:i:s'); // Format directly on the Carbon object
                    $this->savecibildata($postdata,$bureaupayload,$xml_object->SCORE->BureauScore,$userid);
                    
                    return [
                        'cibildata'=>$bureaupayload,
                        'cibilscore'=>$xml_object->SCORE->BureauScore,
                        'cibilfetchdate'=>$xml_object->Header->ReportDate,
                        'reportnumber'=>$xml_object->CreditProfileHeader->ReportNumber
                    ];
                }
                else{
                    return 0;
                }    

        }
        else
        {
            return 0;
        }


    }


    function savecibildata($postdata,$bureaupayload,$bureauScore,$userid){
            $now = Carbon::now();
            $formattedDateTime = $now->format('d-m-Y_H:i:s');

            // Define the file paths for the request and response files
            $basePath = "app-customers/{$userid}/cibil-data/";
            $requestfilePath = $basePath . "{$userid}_{$formattedDateTime}_cibilrequest.xml";
            $responsefilePath = $basePath . "{$userid}_{$formattedDateTime}_cibilresponse.json";

            // Get the content for the files
            $requestfileContent = $postdata;
            $responsefileContent = $bureaupayload;

            // Save the files to S3
            // Storage::disk('s3')->put($requestfilePath, $requestfileContent);
            // Storage::disk('s3')->put($responsefilePath, $responsefileContent);

            // $s3basepath="https://app-backend-images.s3.ap-south-1.amazonaws.com/";


            // Save the files to local disk (storage/app/)
            Storage::put($requestfilePath, $requestfileContent);
            Storage::put($responsefilePath, $responsefileContent);
            

            $requestFullUrl = asset("storage/{$requestfilePath}");
            $responseFullUrl = asset("storage/{$responsefilePath}");

            CibilData::updateOrCreate(
            ['userid'=>$userid],
            [
               'userid'=>$userid,
               'cibilscore'=>$bureauScore,
               'cibilrequestlink'=> $requestFullUrl, //$s3basepath.$requestfilePath,
               'cibilresponselink'=> $responseFullUrl//$s3basepath.$responsefilePath

            ]);

            // Create a new Cibildata record
            $cibildata = new CibilDataLog();
            $cibildata->userid = $userid;
            $cibildata->cibilscore = $bureauScore;
            $cibildata->cibilrequestlink = $requestFullUrl; //$s3basepath.$requestfilePath;
            $cibildata->cibilresponselink = $responseFullUrl;//$s3basepath.$responsefilePath; // Corrected line
            $cibildata->save();



    }

    function replaceEmptyObjects($data) {
        // Check if the data is an array
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // If the value is an empty array, replace it with "None"
                if (is_array($value) && empty($value)) {
                    $data[$key] = "None";
                } else {
                    // Recursively process the array
                    $data[$key] = $this->replaceEmptyObjects($value);
                }
            }
        }
        return $data;
    }

    function calculateAge($dob): int
    {
        if (empty($dob)) {
            return 0;
        }

        try {
            $dob = new DateTime($dob);
            $age = $dob->diff(new DateTime())->y;

            return ($age >= 18 && $age <= 60) ? $age : 0;
        } catch (Exception $e) {
            return 0;
        }
    }


    function getGenderDescription($genderCode): string
    {
        $map = [
            'M' => '1',
            'F' => '2',
            'T' => '3',
        ];
        return $map[strtoupper($genderCode)] ?? '';
    }
    
    function getStateCode($stateName) 
    {
        $states = [
            'JAMMU and KASHMIR' => '01',
            'HIMACHAL PRADESH' => '02',
            'PUNJAB' => '03',
            'CHANDIGARH' => '04',
            'UTTRANCHAL' => '05',
            'HARAYANA' => '06',
            'DELHI' => '07',
            'RAJASTHAN' => '08',
            'UTTAR PRADESH' => '09',
            'BIHAR' => '10',
            'SIKKIM' => '11',
            'ARUNACHAL PRADESH' => '12',
            'NAGALAND' => '13',
            'MANIPUR' => '14',
            'MIZORAM' => '15',
            'TRIPURA' => '16',
            'MEGHALAYA' => '17',
            'ASSAM' => '18',
            'WEST BENGAL' => '19',
            'JHARKHAND' => '20',
            'ORRISA' => '21',
            'CHHATTISGARH' => '22',
            'MADHYA PRADESH' => '23',
            'GUJARAT' => '24',
            'DAMAN and DIU' => '25',
            'DADARA and NAGAR HAVELI' => '26',
            'MAHARASHTRA' => '27',
            'ANDHRA PRADESH' => '28',
            'KARNATAKA' => '29',
            'GOA' => '30',
            'LAKSHADWEEP' => '31',
            'KERALA' => '32',
            'TAMIL NADU' => '33',
            'PONDICHERRY' => '34',
            'ANDAMAN and NICOBAR ISLANDS' => '35',
            'TELANGANA' => '36',
            'APO Address' => '99',
        ];

       return $states[$stateName] ?? '';
    }
   
}

?>
