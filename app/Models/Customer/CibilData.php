<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CibilData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cibilscore',
        'cibilrequestlink',
        'cibilresponselink',
        'manualcibildoc_url',
        'type',
        'old_new',
    ];
}
