<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{

	public function expenses(Request $request)
	{
		try {

			$query = Expense::select('*')
			->orderBy('id', 'desc');

			if(!empty($request->id))
			{
				$query->where('id', $request->id);
			}
			if(!empty($request->items))
			{
				$query->where('items', $request->items);
			}
			if(!empty($request->totalExpense))
			{
				$query->where('totalExpense', $request->totalExpense);
			}
			if(!empty($request->description))
			{
				$query->where('description', $request->description);
			}
			if(!empty($request->expense_date))
			{
				$query->where('expense_date', $request->expense_date);
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
			'items' => 'required',
			'description' => 'required',
			'totalExpense' => 'required|numeric',
			'expense_date' => 'required',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		}  
		DB::beginTransaction();
		try {     
			$expence = new Expense;
			$expence->items = $request->items;
			$expence->description = $request->description;
			$expence->expense_date = $request->expense_date;
			$expence->totalExpense = $request->totalExpense;
			$expence->save();
			DB::commit();
			return prepareResult(true,'Your data has been saved successfully' , $expence, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'items'  => 'required',
			'description' => 'required',
			'totalExpense' => 'required|numeric',
			'expense_date'  => 'required',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		}  
		DB::beginTransaction();
		try {
			$expence = Expense::find($id);
			if(empty($expence))
			{
				return prepareResult(false,'Record Not Found' ,[], 500);
			}
			$expence->items = $request->items;
			$expence->description = $request->description;
			$expence->expense_date = $request->expense_date;
			$expence->totalExpense = $request->totalExpense;
			$expence->save();
			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$expence, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function show($id)
	{
		try {
			$info = Expense::find($id);
			if($info)
			{
				return prepareResult(true,'Record Fatched Successfully' ,$info, 200); 
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

			$info = Expense::find($id);
			if($info)
			{
				$result=$info->delete();
				return prepareResult(true,'Record Deleted Successfully' ,$result, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}    
}
