<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;
class EmployeeAttendence extends Model
{
    use HasFactory,CafeId;
    protected $fillable=[
        'employee_id',
        'attendence',
        'created_by',
        'updated_by',
        'date',
        'cafe_id'
    ];
}
