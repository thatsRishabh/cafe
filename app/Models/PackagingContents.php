<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CafeId;

class PackagingContents extends Model
{
    use HasFactory,CafeId;

    protected $fillable=[
        'name',
        'quantity',
        'unit_id'
    ];
    // working perfectly even after commenting above code

    // public function recipes()
    // {
    //     return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    // }
    public function productName()
    {
        return $this->belongsTo(ProductInfo::class, 'product_info_stock_id', 'id');
    }
    
}
