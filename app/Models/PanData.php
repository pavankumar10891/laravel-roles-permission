<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'panid', 
        'name',
        'gender',
        'dob',
        'pancard_image',
        'old_new'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
