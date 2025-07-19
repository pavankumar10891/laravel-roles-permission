<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycDetail extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'capture_expires_at',
        'kid',
        'token',
        'profile_id',
        'capture_link'
     ];

     public function user(){
        return $this->belongsTo(User::class);
    }
}
