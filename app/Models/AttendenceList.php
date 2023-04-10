<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;


class AttendenceList extends Model
{
    use HasFactory,CafeId;

    protected $fillable=[
        'employee_id',
        'attendence'
    ];
}
