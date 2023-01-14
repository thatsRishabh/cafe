<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;
use App\Models\User;

class CustomerAccountManage extends Model
{
    use HasFactory,CafeId;

    public function customerName()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
}
