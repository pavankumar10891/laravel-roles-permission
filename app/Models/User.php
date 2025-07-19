<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\PanData;
use App\Models\KycDetail;
use App\Models\AAdharData;
use App\Models\Deviceinformation;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'otp',
        'is_verified',
        'device_id',
        'otp_expires_at',
        'last_login_at',
        'firebase_token',
        'otp_expires_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function aadharData()
    {
        return $this->hasOne(AAdharData::class);
    }

    public function devices()
    {
        return $this->hasMany(Deviceinformation::class);
    }
    public function kycDetail()
    {
        return $this->hasOne(KycDetail::class);
    }

    public function panData()
    {
        return $this->hasMany(PanData::class);
    }
}
