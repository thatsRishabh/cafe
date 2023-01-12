<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmployeeAttendence;


class AttendenceList extends Model
{
    use HasFactory;

    protected $fillable=[
        'employee_id',
        'attendence'
    ];
    // working perfectly even after commenting above code

    public function attendence()
    {
        return $this->belongsTo(EmployeeAttendence::class, 'attendence_id', 'id');
    }
}
