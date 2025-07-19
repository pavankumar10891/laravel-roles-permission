<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\v1\BaseController;

class CommonController extends BaseController
{
    public function version()
    {
        return $this->sendSuccess(['version'  => '1.0.0'], 'Version valid');
    }
}
