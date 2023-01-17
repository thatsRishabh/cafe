<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;
use App\Models\Unit;

class ProductInfo extends Model
{
    use HasFactory,CafeId;

    public function unitName()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
