<?php
namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;

trait CafeId {

	protected static function bootStoreId()
    {
    	if (auth()->guard('api')->check()) 
    	{
	        // if user is Admin - Role Id 1 OT cafe_id=1
	        if ((auth()->guard('api')->user()->cafe_id==1)) 
	        {
	        	//nothing heppen
	        }
	        else
	        {	        	
        		static::creating(function ($model) {
		            $model->cafe_id = auth()->guard('api')->user()->cafe_id;
		        });
        		static::addGlobalScope('cafe_id', function (Builder $builder) {
	                $builder->where('cafe_id', auth()->guard('api')->user()->cafe_id);
	            });
	        }
	    }
    }
}