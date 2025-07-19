<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deviceinformation extends Model
{
    use HasFactory;
     protected $fillable = [
        'deviceid',
        'user_id',
        'lat',
        'lon',
        'address',
        'state',
        'city',
        'pincode',
        'firebasetoken',
        'appversion',
        'event',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
