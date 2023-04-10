<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductInfo;
use App\Models\Unit;
use App\Models\ProductStockManage;
use App\Imports\StockImport;
// use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel;

class ProductInfoController extends Controller
{
	public function productInfos(Request $request)
	{
		try {
			$query = ProductInfo::select('*')
			->with('unitName:id,name,minvalue')
			->orderBy('id', 'desc');

			if(!empty($request->id))
			{
				$query->where('product_infos.id',  $request->id);
                // in above we are specifying that it has to match from Product_infos id
			}
			if(!empty($request->unit_id))
			{
				$query->where('product_infos.unit_id',  $request->unit_id);
			}
			if(!empty($request->current_quanitity))
			{
				$query->where('product_infos.current_quanitity',  $request->current_quanitity);
			}
			if(!empty($request->name))
			{
				$query->where('product_infos.name','LIKE', '%'.$request->name.'%');
			}
			if(!empty($request->description))
			{
				$query->where('description', 'LIKE', '%'.$request->description.'%');
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
			$nameCheck = ProductInfo::where('name', $request->name)->first();
			$validation = Validator::make($request->all(), [
				'name' => $nameCheck ? 'required|declined:false' : 'required',
				'unit_id' => 'required|numeric',
				'current_quanitity' => 'required|gte:0.1',
			],
			[
				'name.declined' =>          'Name already exists',
			]);
			if ($validation->fails()) {
				return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

			}       
			$productInfo = new ProductInfo;
			$productInfo->name = $request->name;
			$productInfo->description = $request->description;
			$productInfo->unit_id = $request->unit_id;
			$productInfo->current_quanitity = unitConversion($request->unit_id, $request->current_quanitity);
			$productInfo->save();

            // saving in product stock manage
			$addStockManage = new ProductStockManage;
			$addStockManage->product_id = $productInfo->id;
			$addStockManage->unit_id = $request->unit_id;
			$addStockManage->old_stock = 0;
			$addStockManage->price = $request->price;
			$addStockManage->change_stock = unitConversion($request->unit_id, $request->current_quanitity);
			$addStockManage->new_stock = unitConversion($request->unit_id, $request->current_quanitity);
			$addStockManage->stock_operation ="in";
			$addStockManage->save();

			DB::commit();
			return prepareResult(true,'Your data has been saved successfully' , $productInfo, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$nameCheck = ProductInfo::where('id',  $id)->get('name')->first();
		$validation = Validator::make($request->all(), [
			'name' => $nameCheck ->name == $request->name ? 'required' : 'required|unique:App\Models\ProductInfo,name',
			'unit_id' => 'required|numeric',
			'current_quanitity'           => 'required|gte:0.1',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		}   
		DB::beginTransaction();
		try { 

			$productInfo = ProductInfo::find($id);
			if (empty($productInfo)) {
				return prepareResult(false,'Record Not Found' ,[], 500);
			}
			$productInfo->name = $request->name;
			$productInfo->description = $request->description;
			$productInfo->unit_id = $request->unit_id;
			$productInfo->current_quanitity = unitConversion($request->unit_id, $request->current_quanitity);
			$productInfo->save();

			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$productInfo, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function show($id)
	{
		try {
			$productInfo = ProductInfo::find($id);
			if($productInfo)
			{
				return prepareResult(true,'Record Fatched Successfully' ,$productInfo, 200); 
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
			$productInfo = ProductInfo::find($id);
			if($productInfo)
			{
	            // del in product stock manage
				$delStockManage = ProductStockManage::where('product_id',$id)->delete();
				$result=$productInfo->delete();
				return prepareResult(true,'Record Deleted Successfully' ,$result, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function excelImport(Request $request)
	{
		DB::beginTransaction();
		try {

			$validation = Validator::make($request->all(),
				[
					'file' => 'required',   
				]);
			if ($validation->fails()) {
				return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

			} 
			$file = $request->file;
			$extension = $file->getClientOriginalExtension();
			$allowedExt = ['xlsx'];
			if (!in_array($extension, $allowedExt)) {
				return prepareResult(false, 'Only XLSX file extension allowed.',[], 500);
			}
			$patients = Excel::toArray(new StockImport(), $file);
			$excalRow   = $patients[0];
			$errorShow = false;
			$error = null;
			foreach($excalRow as $key => $stockProduct)
			{
				$nameCheck = ProductInfo::where('name', $stockProduct['item'])->first();

				if(empty($nameCheck))
				{
					$productInfo = new ProductInfo;
					$productInfo->name = $stockProduct['item'];
					$productInfo->current_quanitity = $stockProduct['quantity'];
					$productInfo->save();

                // saving in product stock manage
					$addStockManage = new ProductStockManage;
					$addStockManage->product_id = $productInfo->id;
					$addStockManage->old_stock = 0;
					$addStockManage->price = $stockProduct['price'];
					$addStockManage->change_stock = $stockProduct['quantity'];
					$addStockManage->new_stock =$stockProduct['quantity'];
					$addStockManage->stock_operation ="in";
					$addStockManage->save();
				}
			}  
			DB::commit();
			return prepareResult(true,'Product data successfully imported' , [], 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}
}
