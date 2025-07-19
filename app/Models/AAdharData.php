<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AAdharData extends Model
{
    use HasFactory, SoftDeletes;

    //protected $table = 'a_adhar_data';

    protected $fillable = [
        'user_id',
        'selfie', 
        'uid',
        'fullname',
        'gender',
        'dob',
        'father_name',
        'current_address',
        'current_post_office',
        'current_city',
        'current_state',
        'current_pincode',
        'permanent_address',
        'permanent_post_office',
        'permanent_city',
        'permanent_state',
        'permanent_pincode',
        'firstname',
        'lastname',
        'aadharcard_image',
        'profile_report',
        'old_new'                               
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
