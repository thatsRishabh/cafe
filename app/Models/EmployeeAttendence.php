<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendenceList;
use App\Traits\CafeId;
class EmployeeAttendence extends Model
{
    use HasFactory,CafeId;

    public function employee_attendence()
    {
        return $this->hasMany(AttendenceList::class, 'attendence_id', 'id');
    }

    public function Activity()
    {
        return $this->belongsTo(AttendenceList::class,'id' ,'attendence_id');
    }
}
