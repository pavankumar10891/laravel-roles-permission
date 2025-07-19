<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CibilDataLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cibilscore',
        'cibilrequestlink',
        'cibilresponselink',
        'type',
        'manualcibildoc_url',
        'old_new',
    ];
}
