<?php
namespace App\Traits;

use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;

trait ApiLogger
{
    public function logApi($request, $response)
    {
        ApiLog::create([
            'user_id' => Auth::id(),
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'request' => $request->except(['password', 'password_confirmation']),
            'response' => is_array($response) ? $response : json_decode($response, true),
        ]);
    }
}
