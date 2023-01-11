<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\CafeId;
use App\Models\CafeSetting;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles,CafeId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];


    public function CafeSetting()
    {
        return $this->belongsTo(CafeSetting::class, 'id', 'cafe_id');
    }

    // public function roles()
    // {
    //     return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    // }

}
