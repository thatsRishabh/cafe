<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ProductMenu;
use App\Models\Category;
use App\Models\ProductInfo;
use App\Models\Unit;
use App\Models\Recipe;
use App\Models\RecipeContains;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductMenuController extends Controller
{
	public function categoryProductMenus(Request $request)
	{
		try {
			$query = Category::select('*')->with('productMenu')
			->orderBy('id', 'desc');

			if(!empty($request->id))
			{
				$query->where('id', $request->id);
			}
			if(!empty($request->name))
			{
				$query->where('name', 'LIKE', '%'.$request->name.'%');
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

		} 
		catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Error while fatching Records' ,$e->getMessage(), 500);
		}
	}

	public function productMenus(Request $request)
	{
		try {
			$query = ProductMenu::select('*')->with('category:id,name');
			if(!empty($request->priority_rank)){
				$query = $query->orderBy('priority_rank', 'asc');
			}else{ 
				$query = $query->orderBy('id', 'desc');
			}
			if(!empty($request->id))
			{
				$query->where('id', $request->id);
			}
			if(!empty($request->name))
			{
				$query->where('name', 'LIKE', '%'.$request->name.'%');
			}
			if(!empty($request->category_id))
			{
				$query->where('category_id', $request->category_id);
			}
			if(!empty($request->price))
			{
				$query->where('price', $request->price);
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
		$validation = Validator::make($request->all(), [
			"product_list.*.name"  => "required", 
			"product_list.*.price"  => "required|numeric", 
			"product_list.*.order_duration"  => "required|numeric", 
			"product_list.*.category_id"  => "required|numeric", 
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		} 
		DB::beginTransaction();
		try {    
			$productMenu = new ProductMenu;
			foreach ($request->product_list as $key => $product) 
			{
				$addProduct = new ProductMenu;
				$addProduct->product_info_stock_id =  $product['product_info_stock_id'];
				$addProduct->without_recipe =  $product['without_recipe'];
				$addProduct->quantity = $product['quantity'];
				$addProduct->name =  $product['name'];
				$addProduct->description =  $product['description'];
				$addProduct->price =  $product['price'];
				$addProduct->order_duration =  $product['order_duration'];
				$addProduct->priority_rank =  $product['priority_rank'] ? $product['priority_rank'] : "10000" ;
				$addProduct->category_id =  $product['category_id'];

				if(!empty($product['image']))
				{
					$file=$product['image'];
					$filename=time().'.'.$file->getClientOriginalExtension();
					if ($file->move('assets/product_photos', $filename)) {
						$addProduct->image=env('CDN_DOC_URL').'assets/product_photos/'.$filename.'';
					}
				}
				$addProduct->save();
			}

			DB::commit();
			return prepareResult(true,'Your data has been saved successfully' , $productMenu, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			"name"  => "required",  
			"price"  => "required|numeric", 
			"order_duration"  => "required|numeric", 
			"category_id"  => "required|numeric",
			'image'                       => $request->hasFile('image') ? 'mimes:jpeg,jpg,png,gif|max:10000' : '',
		]);

		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		} 
		DB::beginTransaction();
		try { 
			$productMenu = ProductMenu::find($id);
			if(empty($productMenu))
			{
				return prepareResult(false,'Record Not Found' ,[], 500);
			}

			if(!empty($request->image))
			{
				if(gettype($request->image) == "string"){
					$productMenu->image = $request->image;
				}
				else{
					if ($request->hasFile('image')) {
						$file = $request->file('image');
						$filename=time().'.'.$file->getClientOriginalExtension();
						if ($file->move('assets/product_photos', $filename)) {
							$productMenu->image=env('CDN_DOC_URL').'assets/product_photos/'.$filename.'';
						}
					}
				}
			}

			$productMenu->product_info_stock_id = $request->product_info_stock_id;
			$productMenu->without_recipe = $request->without_recipe;
			$productMenu->quantity = $request->quantity;
			$productMenu->name = $request->name;
			$productMenu->order_duration = $request->order_duration;
			$productMenu->description = $request->description;
			$productMenu->priority_rank = $request->priority_rank;
			$productMenu->category_id = $request->category_id;
			$productMenu->price = $request->price;
			$productMenu->save();

			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$productMenu, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function show($id)
	{
		try {
			$productMenu = ProductMenu::find($id);
			if($productMenu)
			{
				return prepareResult(true,'Record Fatched Successfully' ,$productMenu, 200); 
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
			$productMenu = ProductMenu::find($id);
			if($productMenu)
			{
				$result=$productMenu->delete();
				return prepareResult(true,'Record Deleted Successfully' ,$result, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

}
