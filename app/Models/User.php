<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
    protected $guard_name = 'api';
    protected $fillable = [
        'name',
        'email',
        'password',
        'cafe_id',
        'parent_id',
        'mobile',
        'designation',
        'document_type',
        'document_number',
        'address',
        'joining_date',
        'birth_date',
        'gender',
        'salary',
        'salary_balance',
        'image',
        'account_balance',
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


    public function cafeSetting()
    {
        return $this->hasOne(CafeSetting::class, 'cafe_id', 'id');
    }

    public function cafeSubscription()
    {
        return $this->hasMany(CafeSetting::class, 'subscription_id', 'cafe_id');
    }

    // public function roles()
    // {
    //     return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    // }

}
