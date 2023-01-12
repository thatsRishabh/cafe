<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderContain;
use App\Traits\CafeId;
class Order extends Model
{
    use HasFactory,CafeId;

    public function orderContains()
    {
        return $this->hasMany(OrderContain::class, 'order_id', 'id');
    }
}