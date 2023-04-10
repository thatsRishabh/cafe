<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\SalaryManagement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
// use App\Models\Employee;
use App\Models\User;

class SalaryManagementController extends Controller
{
    //
	public function salaryManagements(Request $request)
	{
		try {

			$query = SalaryManagement::select('*')
			->with('employee:id,name,salary')
			->orderBy('id', 'desc');

			if(!empty($request->id))
			{
				$query->where('id', $request->id);
			}
			if(!empty($request->employee_id))
			{
				$query->where('employee_id', $request->employee_id);
			}

                    // date wise filter from here
			if(!empty($request->from_date) && !empty($request->end_date))
			{
				$query->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
			}

			if(!empty($request->from_date) && !empty($request->end_date) && !empty($request->employee_id))
			{
				$query->where('employee_id', $request->employee_id)->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
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
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function store(Request $request)
	{
		$validation = Validator::make($request->all(), [
			'paid_amount'                   => 'required|numeric',
			'employee_id'                         => 'required|numeric|exists:users,id',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		}    
		DB::beginTransaction();
		try {   
			$old = User::where('id', $request->employee_id)->get('salary_balance')->first();
			$salaryManagement = new SalaryManagement;
			$salaryManagement->employee_id = $request->employee_id;

            // storing old salary from employee table
			$salaryManagement->previous_balance = $old->salary_balance;
			$salaryManagement->paid_amount = $request->paid_amount;

            // stock in/out calculation
			$salaryManagement->new_balance =  $old->salary_balance - $request->paid_amount;
			$salaryManagement->save();

            // updating the productinfo table as well
			$updateBalance = User::find( $request->employee_id);
			$updateBalance->salary_balance =  $salaryManagement->new_balance;
			$updateBalance->save();

			DB::commit();
			return prepareResult(true,'Your data has been saved successfully' , $salaryManagement, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'paid_amount'                   => 'required|numeric',
			'employee_id'                         => 'required|numeric|exists:users,id',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		} 
		DB::beginTransaction();
		try { 

			$old = User::where('id', $request->employee_id)->get('salary_balance')->first();


			$salaryManagement = SalaryManagement::find($id);
			if (empty($salaryManagement)) {

				return prepareResult(false,'Record Not Found' ,[], 500);
			}
			$salaryManagement->employee_id = $request->employee_id;
			$salaryManagement->previous_balance = $salaryManagement->previous_balance;
			$salaryManagement->paid_amount = $request->paid_amount;
			$salaryManagement->new_balance = $salaryManagement->previous_balance - $request->paid_amount;
			$salaryManagement->save();

            // updating the productinfo table as well
			$updateBalance = User::find( $request->employee_id);
			$updateBalance->salary_balance =  $salaryManagement->new_balance;
			$updateBalance->save();
			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$salaryManagement, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function show($id)
	{
		try {
			$salaryManagement = SalaryManagement::find($id);
			if($salaryManagement)
			{
				return prepareResult(true,'Record Fatched Successfully' ,$salaryManagement, 200); 
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
			$salaryManagement = SalaryManagement::find($id);
			if($salaryManagement)
			{
				$result = $salaryManagement->delete();
				return prepareResult(true,'Record Deleted Successfully' ,$result, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}
}
