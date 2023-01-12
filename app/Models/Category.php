<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;
class Category extends Model
{
    use HasFactory,CafeId;

    // public function subCategory()
    // {
    //      return $this->hasMany(self::class,'parent_id','id');
    // }

    public function productMenu()
    {
        return $this->hasMany(ProductMenu::class, 'category_id', 'id');
    }
}
