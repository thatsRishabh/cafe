<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\EmployeeAttendence;
// use App\Models\Employee;
use App\Models\AttendenceList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\PDF;
// use PDF;
use App\Models\User;

class EmployeeAttendenceController extends Controller
{

	public function employeeAttendences(Request $request)
	{
		try {
			$query = EmployeeAttendence::select('*')
			->with('attendenceLists:attendence_id,employee_id,attendence')
			->orderBy('id', 'desc');

			if(!empty($request->id))
			{
				$query->where('id', $request->id);
			}
			if(!empty($request->date))
			{
				$query->where('date', $request->date);
			}
			if(!empty($request->employee_id))
			{
				$query->where('employee_id', $request->employee_id);
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
			'date' => 'required',

		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		}
		DB::beginTransaction();
		try {
			if($request->employee_attendence){
				foreach ($request->employee_attendence as $key => $recipe1) {
					$currentDate= date("Y-m-d");
					$oldValue1 = AttendenceList::whereDate('date', '=',  $request->date)->where('employee_id', $recipe1['employee_id'])->get('employee_id')->first();

					$validation = Validator::make($request->all(),[      
						"employee_attendence.*.employee_id"  => $oldValue1 ? 'required|declined:false' : 'required', 
					],
					[
						'employee_attendence.*.employee_id.declined' => 'Attendence already exists',
					]
				);
					if ($validation->fails()) {
						return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

					} 
				}
			} 

			$info = new EmployeeAttendence;
			$info->date = $request->date;
			$info->save();

			foreach ($request->employee_attendence as $key => $attendence) {
				$addAttendence = new AttendenceList;
				$addAttendence->attendence_id =  $info->id;
				$addAttendence->date = $request->date;
				$addAttendence->employee_id = $attendence['employee_id'];
				$addAttendence->attendence = $attendence['attendence'];
				$addAttendence->save();     
			}

			DB::commit();
			$info['attendence_lists'] = $info->attendenceMethod;
			return prepareResult(true,'Your data has been saved successfully' , $info, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'date' => 'required',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		} 
		DB::beginTransaction();
		try {
			$info = EmployeeAttendence::find($id);
			$info->date = $request->date;
			$info->save();

			$deletOld = AttendenceList::where('attendence_id', $id)->delete();
			foreach ($request->employee_attendence as $key => $attendence) {
				$addAttendence = new AttendenceList;
				$addAttendence->attendence_id =  $id;
				$addAttendence->date = $request->date;
				$addAttendence->employee_id = $attendence['employee_id'];
				$addAttendence->attendence = $attendence['attendence'];
				$addAttendence->save();      
			}
			DB::commit();
			$info['attendence_lists'] = $info->attendenceMethod;
			return prepareResult(true,'Your data has been Updated successfully' ,$info, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function show($id)
	{
		try {
			$info = EmployeeAttendence::find($id);
			if($info)
			{
				return prepareResult(true,'Record Fatched Successfully' ,$info, 200); 
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
			$info = EmployeeAttendence::find($id);
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

	public function employeeID()
	{
		try {
			$data = User::select('id as employee_id')->where('role_id', 3)->get();
			return prepareResult(true,'Record Fatched Successfully' ,$data, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}
	public function dateWiseSearch(Request $request) {
		try {
			$info = AttendenceList::where('employee_id', $request->employee_id);
			if(!empty($request->from_date) && !empty($request->end_date))
			{
				$info->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
			}
			elseif(!empty($request->from_date) && empty($request->end_date))
			{
				$info->whereDate('created_at', '>=', $request->from_date);
			}
			elseif(empty($request->from_date) && !empty($request->end_date))
			{
				$info->whereDate('created_at', '<=', $request->end_date);
			}
			$result= $info->get();
			return prepareResult(true,'Record Fatched Successfully' ,$result, 200); 
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function monthlyAttendence(Request $request) 
	{
		try {
			$data = [];
			$user = User::find($request->employee_id);
			if (empty($user)) {
				return prepareResult(false,'Employee Not Found' ,[], 500);
			}
			$date = $request->year_month."-t";
			$joining_date = $user->joining_date;
			$joining_dates = substr($joining_date, -13,-6);
			if(($request->year_month) < ($joining_dates))
			{
				return prepareResult(false,'Employee did not Joined on given date' ,null, 500); 
			}
			$total_days = cal_days_in_month(CAL_GREGORIAN,date('m',strtotime($date)),date('Y',strtotime($date)));
			$present_days = AttendenceList::where('employee_id', $user->id)->where('attendence',2)->whereMonth('date', $date )->whereYear('date', $date )->count();
			$half_days = AttendenceList::where('employee_id', $user->id)->where('attendence',3)->whereMonth('date', $date )->whereYear('date', $date )->count();
			$absent_days = $total_days - $present_days - ($half_days/2);
			$user_salary = $user->salary;
			$data['year_month'] = $request->year_month;
			$data['days_in_month'] = $total_days;
			$data['total_days_present'] =  $present_days;
			$data['total_days_halfday'] = $half_days;
			$data['total_days_absent'] = $absent_days;
			$data['employeeSalary'] = $user_salary;
			$data['currentMonthSalary'] = $user_salary * ($present_days/$total_days);
			return prepareResult(true,'MonthWise Data Fatched Successfully' ,$data, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}
}
