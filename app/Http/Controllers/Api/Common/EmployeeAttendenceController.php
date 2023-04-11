<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\EmployeeAttendence;
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
			if(!empty($request->from_date))
			{
				$info->whereDate('created_at', '>=', $request->from_date);
			}
			if(!empty($request->end_date))
			{
				$info->whereDate('created_at', '<=', $request->end_date);
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
			return prepareResult(true,'Record Fatched Successfully' ,['employee_attendence'=>$query], 200);

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
			$date = $request->date;
			$attendence_ids = [];
			if($request->employee_attendence){
				foreach ($request->employee_attendence as $key => $value) {
					$attendenceCheck = EmployeeAttendence::whereDate('date', $date)->where('employee_id', $value['employee_id'])->get('employee_id')->first();

					$validation = Validator::make($request->all(),[      
						"employee_attendence.*.employee_id"  => $attendenceCheck ? 'required|declined:false' : 'required', 
					],
					[
						'employee_attendence.*.employee_id.declined' => 'Attendence already exists',
					]);
					if ($validation->fails()) {
						return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
					} 
					$attendenceList = new EmployeeAttendence;
					$attendenceList->cafe_id =  auth()->id();
					$attendenceList->date = $date;
					$attendenceList->created_by = auth()->id();
					$attendenceList->employee_id = $value['employee_id'];
					$attendenceList->attendence = $value['attendence'];
					$attendenceList->save();
					$attendence_ids[] = $attendenceList->id; 
				}
			}
			DB::commit();
			$data = EmployeeAttendence::whereIn('id',$attendence_ids)->get();
			return prepareResult(true,'Your data has been saved successfully' , $data, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'date' => 'required',
			'employee_id'=>'required',
			'attendence'=>'required'
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		} 
		DB::beginTransaction();
		try {
			$attendenceCheck = EmployeeAttendence::whereDate('date', $request->date)->where('employee_id', $request->employee_id)->where('id','!=',$id)->first();

			$validation = Validator::make($request->all(),[      
				"employee_id"  => $attendenceCheck ? 'required|declined:false' : 'required', 
			],
			[
				'employee_id.declined' => 'Attendence already exists',
			]);
			if ($validation->fails()) {
				return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
			} 
			$attendenceList = EmployeeAttendence::find($id);
			if(empty($attendenceList))
			{
				return prepareResult(false,'Record not found' ,[], 500);
			}
			$attendenceList->cafe_id =  auth()->id();
			$attendenceList->date = $request->date;
			$attendenceList->employee_id = $request->employee_id;
			$attendenceList->attendence = $request->attendence;
			$attendenceList->updated_by = auth()->id();
			$attendenceList->save();
			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$attendenceList, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function multipleUpdate(Request $request)
	{
		$validation = Validator::make($request->all(), [
			'date' => 'required',
			'employee_attendence'=>'required|array',
		]);
		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		} 
		DB::beginTransaction();
		try {
			$attendence_ids = [];
			if($request->employee_attendence){
				foreach ($request->employee_attendence as $key => $value) {
					// $attendenceCheck = EmployeeAttendence::whereDate('date', $request->date)->where('employee_id', $request->employee_id)->where('id','!=',$value['id'])->get('employee_id')->first();
					// $validation = Validator::make($request->all(),[      
					// 	"employee_attendence.*.employee_id"  => $attendenceCheck ? 'required|declined:false' : 'required', 
					// ],
					// [
					// 	'employee_attendence.*.employee_id.declined' => 'Attendence already exists',
					// ]);
					// if ($validation->fails()) {
					// 	return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
					// } 
					$attendenceList = EmployeeAttendence::find($value['id']);
					if(empty($attendenceList))
					{
						return prepareResult(false,'Record not found' ,[], 500);
					}
					$attendenceList->date = $request->date;
					$attendenceList->employee_id = $value['employee_id'];
					$attendenceList->attendence = $value['attendence'];
					$attendenceList->updated_by = auth()->id();
					$attendenceList->save();
					$attendence_ids[] = $attendenceList->id; 
				}
			}
			$data = EmployeeAttendence::whereIn('id',$attendence_ids)->get();
			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$data, 200);
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
				$result = $info->delete();
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
			$data = [];
			$data['employee_attendence'] = User::select('id as employee_id')->where('role_id', 3)->get();
			return prepareResult(true,'Record Fatched Successfully' ,$data, 200);
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
			$absent_days = EmployeeAttendence::where('employee_id', $user->id)->where('attendence',1)->whereMonth('date', $date )->whereYear('date', $date )->count();
			$half_days = EmployeeAttendence::where('employee_id', $user->id)->where('attendence',3)->whereMonth('date', $date )->whereYear('date', $date )->count();
			$present_days = $total_days - $absent_days - ($half_days/2);
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
