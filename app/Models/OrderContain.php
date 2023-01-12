<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Traits\CafeId;
class OrderContain extends Model
{
    use HasFactory,CafeId;
    protected $fillable=[
        'name',
        'quantity',
        'category_id',
        'product_menu_id',
        'price',
        'instructions',
        'unit_id',
        'order_duration',
        'netPrice',
    ];
    
    // working perfectly even after commenting above code

    public function  Orders()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    protected $casts = [
        'created_at' => 'date',
    ];

}
