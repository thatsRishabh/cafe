<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{

	public function categories(Request $request)
	{
		try {

			$query = Category::select('*')
			->orderBy('id', 'desc');
			if(!empty($request->id))
			{
				$query->where('id', $request->id);
			}
			if(!empty($request->name))
			{
				$query->where('name', $request->name);
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

			return prepareResult(true,'Records Fatched Successfully' ,$query, 200);

		} 
		catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Error while fatching Records' ,$e->getMessage(), 500);
		}
	}

	public function store(Request $request)
	{
		$validation = Validator::make($request->all(),  [
			'image' => $request->image ? 'mimes:jpeg,jpg,png,gif|max:10000' : '',
			'name' => 'required|unique:categories',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		} 
		DB::beginTransaction();
		try { 
			$category = new Category;
			if ($request->hasFile('image')) {
				$file = $request->file('image');
				$filename=time().'.'.$file->getClientOriginalExtension();
				if ($file->move('assets/category_photos', $filename)) {
					$category->image=env('CDN_DOC_URL').'assets/category_photos/'.$filename.'';
				}
			}
			$category->name = $request->name;
			$category->save();

			DB::commit();
			return prepareResult(true,'Your data has been saved successfully' , $category, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'name' => 'required|unique:categories,name,'.$id,
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		} 
		DB::beginTransaction();
		try {      
			$category = Category::find($id);
			if (empty($category)) {
				return prepareResult(false,'Record not found' ,[], 500);
			}
			if(!empty($request->image))
			{
				if(gettype($request->image) == "string"){
					$category->image = $request->image;
				}
				else{
					if ($request->hasFile('image')) {
						$file = $request->file('image');
						$filename=time().'.'.$file->getClientOriginalExtension();
						if ($file->move('assets/category_photos', $filename)) {
							$category->image=env('CDN_DOC_URL').'assets/category_photos/'.$filename.'';
						}
					}
				}
			}
			$category->name = $request->name;
			$category->save();
			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$category, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function show($id)
	{
		try {
			$category = Category::find($id);
			if($category)
			{
				return prepareResult(true,'Record Fatched Successfully' ,$category, 200); 
			}
			return prepareResult(false,'Record not found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function destroy($id)
	{
		try {
			$category = Category::find($id);
			if($category)
			{
				$result=$category->delete();
				return prepareResult(true,'Record Deleted Successfully' ,$result, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}
}
