<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kyclog extends Model
{
    use HasFactory;

    protected $fillable = [
        'request', 
        'response',
        'user_id',
        'old_new'
    ];
}
