<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;
use App\Models\PackagingContents;
use App\Models\Category;
use App\Models\ProductInfo;

class Packaging extends Model
{
    use HasFactory,CafeId;

    public function packagingMaterial()
    {
        return $this->hasMany(PackagingContents::class, 'packaging_id', 'id');
    }

    public function categoryName()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    // public function productName()
    // {
    //     return $this->belongsTo(ProductInfo::class, 'product_info_stock_id', 'id');
    // }
    
}
