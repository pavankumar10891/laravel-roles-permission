<?php
namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait Smsnotification{
    public function sendSmsNotification($to, $text){
        //https://api.smartping.ai/fe/api/v1/send?username=meltron.trans&password=vhtoe&unicode=false&from=MSPLON&to=7610031383&text=258825 
        try {
            // $response = Http::post('https://api.smartping.ai/fe/api/v1/send', [
            //     'username' => 'meltron.trans',
            //     'password' => 'vhtoe',
            //     'unicode' => false,
            //     'from' => 'MSPLON',
            //     'to' => $to,
            //     'text' => urlencode($text),
            //     'dltContentId' => $dltContentId,
            // ]);
            $newText = urlencode($text);
            $response = Http::get('https://api.smartping.ai/fe/api/v1/send?username=meltron.trans&password=vhtoe&unicode=false&from=MSPLON&to='.$to.'&text='.$newText);

            if ($response->successful()) {
                return $response->json(); // return full response
            } else {
                return ['error' => 'SMS sending failed', 'details' => $response->body()];
            }
        } catch (\Exception $e) {
            return ['error' => 'Exception occurred', 'message' => $e->getMessage()];
        }
    }
}

