<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;

class EmployeeLeave extends Model
{
    use HasFactory,CafeId;
    protected $fillable = ['cafe_id','employee_id','month','year','no_of_leaves'];
}
