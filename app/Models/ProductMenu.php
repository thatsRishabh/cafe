<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;
class ProductMenu extends Model
{
    use HasFactory,CafeId;

    // public function Parent()
    // {
    //       return $this->belongsTo(self::class,'parent_id','id');
    // }
    // public function children()
    // {
    //      return $this->hasMany(self::class, 'parent_id');
    // }

    public function halfPrice()
    {
         return $this->hasOne(self::class,'parent_id','id');
    }
}
