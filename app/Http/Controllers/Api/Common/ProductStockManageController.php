<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ProductStockManage;
use App\Models\ProductMenu;
use App\Models\Unit;
use App\Models\ProductInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Enums\ServerStatus;
use Illuminate\Validation\Rules\Enum;

class ProductStockManageController extends Controller
{
	public function productStockManages(Request $request)
	{
		try {
			$query = ProductStockManage::select('*')
			->with('unitName:id,name,minvalue','productName:id,name')
			->orderBy('id', 'desc');

			if(!empty($request->id))
			{
				$query->where('product_stock_manages.id', $request->id);
			}
            // below query is to search inside join function 
			$product_id = $request->product_id;
			if(!empty($request->product_id))
			{
				$query->whereHas('productName',function ($query) use ($product_id) {
					$query->Where('product_id', 'LIKE', "%{$product_id}%");
				}); 
			}   

			if(!empty($request->stock_operation))
			{
				$query->where('product_stock_manages.stock_operation', $request->stock_operation);
			}
			if(!empty($request->product))
			{
				$query->where('product', 'LIKE', '%'.$request->product.'%');
			}

            // date wise filter from here
			if(!empty($request->from_date) && !empty($request->end_date))
			{
				$query->whereDate('product_stock_manages.created_at', '>=', $request->from_date)->whereDate('product_stock_manages.created_at', '<=', $request->end_date);
			}

			if(!empty($request->from_date) && !empty($request->end_date) && !empty($request->product_id))
			{
				$query->where('product_id', $request->product_id)->whereDate('product_stock_manages.created_at', '>=', $request->from_date)->whereDate('product_stock_manages.created_at', '<=', $request->end_date);
			}

			if(!empty($request->per_page_record))
			{
				$perPage = $request->per_page_record;
				$page = $request->input('page', 1);
				$total = $query->count();
				$result = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

				$pagination =  [
					'data' => $result,
					'total' => $total,
					'current_page' => $page,
					'per_page' => $perPage,
					'last_page' => ceil($total / $perPage)
				];
				$query = $pagination;
			}
			else
			{
				$query = $query->get();
			}
			return prepareResult(true,'Record Fatched Successfully' ,$query, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function store(Request $request)
	{
		DB::beginTransaction();
		try {
			$old = ProductInfo::where('product_infos.id', $request->product_id)->get('current_quanitity')->first();

			$validation = Validator::make($request->all(), [
				'stock_operation'                => 'required',
				'product_id'                   => 'required|numeric',
				'change_stock'                      => (strtolower($request->stock_operation) == "out") 
				&& ($old->current_quanitity) < unitConversion($request->unit_id, ($request->change_stock))
				? 'required|declined:false' : 'required|gte:1',
				'unit_id'                      => unitSimilarTypeCheck($request->unit_id,$request->product_id),

			],
			[
				'change_stock.declined' => 'Low quantity in stock',
				'unit_id.declined' => 'Invalid Unit Type'
			]);
			if ($validation->fails()) { 
				return prepareResult(false,$validator->errors()->first() ,$validation->errors(), 500);

			}       
         	// getting old stock value

			$old = ProductInfo::where('product_infos.id', $request->product_id)->get('current_quanitity')->first();
			$info = new ProductStockManage;
			$info->product_id = $request->product_id;
			$info->unit_id = $request->unit_id;
			$info->old_stock = $old->current_quanitity;
            // storing old stock from product infos stock table
			$info->price = $request->price;
			$info->change_stock =  unitConversion($request->unit_id, $request->change_stock);

            // stock in/out calculation
			$info->new_stock = strtolower($request->stock_operation) == "in" 
			? $old->current_quanitity + unitConversion($request->unit_id, $request->change_stock) 
			: $old->current_quanitity - unitConversion($request->unit_id, $request->change_stock);

			$info->stock_operation = $request->stock_operation;
			$info->save();

            // updating the productinfo table as well
			$updateStock = ProductInfo::find( $request->product_id);
			$updateStock->current_quanitity = $info->new_stock;
			$updateStock->save();

			DB::commit();
			return prepareResult(true,'Your data has been saved successfully' , $info, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'stock_operation'=> 'required',
			'product_id' => 'required|numeric',
			'old_stock' => 'nullable|numeric',
			'new_stock' => 'nullable|numeric',
			'unit_id' => unitSimilarTypeCheck($request->unit_id,$request->product_id),
		],
		[
			'unit_id.declined' => 'Invalid Unit Type',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		}  
		DB::beginTransaction();
		try { 

			$oldStockValue =ProductStockManage::find($id);
			$unitData =Unit::find($oldStockValue->unit_id);

			$updateStock = ProductInfo::find( $request->product_id);
			$checkQuanitity = strtolower($oldStockValue->stock_operation) == "in" 
			? $oldStockValue->new_stock - ($oldStockValue->change_stock * $unitData->minvalue)  
			: $oldStockValue->new_stock + ($oldStockValue->change_stock * $unitData->minvalue);

			$validator = Validator::make($request->all(), [
				'change_stock'                      => (strtolower($request->stock_operation) == "out") 
				&& (($checkQuanitity) < unitConversion($request->unit_id, ($request->change_stock)))
				? 'required|declined:false' : 'required|gte:1',

			],
			[
				'change_stock.declined' => 'low quantity in stock',

			]);

			if ($validator->fails()) {
				return prepareResult(false,$validator->errors()->first() ,$validator->errors(), 500);
			}   

			$oldStockValue =ProductStockManage::find($id);

			if($oldStockValue->change_stock == $oldStockValue->new_stock){

				if(strtolower($request->stock_operation) == "out"){
					return prepareResult(false,'Only "Stock IN operation allowed for this product"' ,[], 500);
				}

				$info = ProductStockManage::find($id);
				$info->change_stock = unitConversion($request->unit_id, $request->change_stock);
				$info->new_stock = strtolower($request->stock_operation) == "in" 
				? unitConversion($request->unit_id, $request->change_stock) 
				: " ";
				$info->unit_id = $request->unit_id;
				$info->save();

            	// updating the productinfo table as well
				$updateStock = ProductInfo::find( $request->product_id);
				$updateStock->current_quanitity = $info->new_stock;
				$updateStock->unit_id = $request->unit_id;
				$updateStock->save();
			}
			else{
				$unitData =Unit::find($oldStockValue->unit_id);


              	// restoring productinfo old stock to previous value after kg/gram/dozen conversion
				$updateStock = ProductInfo::find( $request->product_id);
				$updateStock->current_quanitity = strtolower($oldStockValue->stock_operation) == "in" 
				? $oldStockValue->new_stock - ($oldStockValue->change_stock * $unitData->minvalue)  
				: $oldStockValue->new_stock + ($oldStockValue->change_stock * $unitData->minvalue);
				$updateStock->save();

            	//  getting old stock value
				$old = ProductInfo::where('product_infos.id', $request->product_id)->get('current_quanitity')->first();

				$info = ProductStockManage::find($id);
				$info->product_id = $request->product_id;
				$info->unit_id = $request->unit_id;
				$info->price = $request->price;
             	// storing old stock from product infos stock table
				$info->old_stock = $old->current_quanitity;
				$info->change_stock = unitConversion($request->unit_id, $request->change_stock);

              	// stock in/out calculation
				$info->new_stock = strtolower($request->stock_operation) == "in" 
				? $old->current_quanitity + unitConversion($request->unit_id, $request->change_stock) 
				: $old->current_quanitity - unitConversion($request->unit_id, $request->change_stock);
            	// : $old->current_quanitity = - unitConversion($request->unit_id, $request->change_stock);

				$info->stock_operation = $request->stock_operation;
				$info->save();

             	// updating the productinfo table as well
				$updateStock = ProductInfo::find( $request->product_id);
				$updateStock->current_quanitity = $info->new_stock;
				$updateStock->save();
			}
			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$info, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function show($id)
	{
		try {

			$stockInfo = ProductStockManage::find($id);
			if($stockInfo)
			{
				return prepareResult(true,'Record Fatched Successfully' ,$stockInfo, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function destroy($id)
	{
		try {
			DB::beginTransaction();
			$info = ProductStockManage::find($id);
			if($info)
			{
				$oldStockValue =ProductStockManage::find($id);
				$unitData =Unit::find($oldStockValue->unit_id);

				if($oldStockValue->change_stock == $oldStockValue->new_stock)
				{
					$updateStock = ProductInfo::find( $oldStockValue->product_id);
					$updateStock->current_quanitity = 0;
					$updateStock->save();
				}
				else
				{
              		// restoring productinfo old stock to previous value after kg/gram/dozen conversion
					$updateStock = ProductInfo::find( $oldStockValue->product_id);
					$updateStock->current_quanitity = strtolower($oldStockValue->stock_operation) == "in" 
					? $oldStockValue->new_stock - ($oldStockValue->change_stock * $unitData->minvalue)  
					: $oldStockValue->new_stock + ($oldStockValue->change_stock * $unitData->minvalue);
					$updateStock->save();
				}
				$result=$info->delete();
				DB::commit();
				return prepareResult(true,'Record Deleted Successfully' ,$result, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

}
