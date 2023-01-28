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

    public function searchEmployeeAttendence(Request $request)
    {
        try {

            $query = EmployeeAttendence::select('*')
                            ->with('employee_attendence:attendence_id,employee_id,attendence')
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
                    //  // date wise filter from here
                    //  if(!empty($request->from_date) && !empty($request->end_date))
                    // {
                    //     $query->whereDate('employee_attendences.created_at', '>=', $request->from_date)->whereDate('employee_attendences.created_at', '<=', $request->end_date);
                    // }
        
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


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                        'date'                    => 'required',
                       
                    ]);
            
        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }  
        
               if($request->employee_attendence){

            foreach ($request->employee_attendence as $key => $recipe1) {
                $currentDate= date("Y-m-d");
                // $oldValue1 = AttendenceList::whereDate('created_at', '=',  $currentDate)->where('employee_id', $recipe1['employee_id'])->get('employee_id')->first();
                $oldValue1 = AttendenceList::whereDate('date', '=',  $request->date)->where('employee_id', $recipe1['employee_id'])->get('employee_id')->first();
              
                $validation = Validator::make($request->all(),[      
                    "employee_attendence.*.employee_id"  => $oldValue1 ? 'required|declined:false' : 'required', 
                    
                 ],
                 [
                     'employee_attendence.*.employee_id.declined' => 'Attendence already exists',
                 ]
             );

             
            if ($validation->fails()) {
                return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
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
            DB::rollback();
            return prepareResult(false,'Your data has not been saved' ,$e->getMessage(), 500);
            
        }
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                        'date'                    => 'required',
                          
                    ]);
            
           if ($validation->fails()) {
                        return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
                       
           }    


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
            DB::rollback();
            return prepareResult(false,'Your data has not been Updated' ,$e->getMessage(), 500);
            
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
            return prepareResult(false,'Error while fatching Records' ,[], 500);
        } catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'something_went_wrong' ,$e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            
            $info = EmployeeAttendence::find($id);
            if($info)
            {
                $result=$info->delete();
                return prepareResult(true,'Record Id Deleted Successfully' ,$result, 200); 
            }
            return prepareResult(false,'Record Id Not Found' ,[], 500);
        } catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'something_went_wrong' ,$e->getMessage(), 500);
        }
    }

    public function employeeID()
    {
        try {

            $data = [];
            
            $data['employee_attendence']= User::select('id as employee_id')->where('role_id', 3)->get();
           
        //    $data['employeeSalary'] = $employeeData->salary;

                // return response(prepareResult(false, $info, trans('translate.fetched_records')), config('httpcodes.success'));
                return prepareResult(true,'Record Fatched Successfully' ,$data, 200); 
        
        } catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'something_went_wrong' ,$e->getMessage(), 500);
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
        if($info)
            {
                $result= $info->get();
                return prepareResult(true,'Record Fatched Successfully' ,$result, 200); 
                
            }
            return prepareResult(false,'Error while fatching Records' ,[], 500);
        } catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'something_went_wrong' ,$e->getMessage(), 500);
        }
    }
    
    public function monthlyAttendence(Request $request) {
        try {
    
            $data = [];
    
            // use of template literal while adding date
            $data['days_in_month'] = cal_days_in_month(CAL_GREGORIAN, substr($request->year_month, 5,6), substr($request->year_month, 0,4));
            $data['year_month']=$request->year_month;
    
            $data['total_days_absent'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',1)->whereDate('date', '>=', $request->year_month.'-01' )->whereDate('date', '<=', $request->year_month.'-31')->count();

            $data['total_days_halfday'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',3)->whereDate('date', '>=', $request->year_month.'-01' )->whereDate('date', '<=', $request->year_month.'-31')->count();

            // $data['total_days_present'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',2)->whereDate('date', '>=', $request->year_month.'-01' )->whereDate('date', '<=', $request->year_month.'-31')->count();

            $data['total_days_present'] =  $data['days_in_month'] - $data['total_days_absent'] -$data['total_days_halfday']/2;
    
    
           
    
            // $data['total_days_absent'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',1)->whereDate('created_at', '>=', $request->year_month.'-01' )->whereDate('created_at', '<=', $request->year_month.'-31')->count();
    
            
    
           
           $employeeData = User::where('id', $request->employee_id)->get('salary')->first();
            $data['employeeSalary'] = $employeeData->salary;
            $data['currentMonthSalary'] = $data['employeeSalary'] - ( $data['total_days_absent']*$data['employeeSalary']/ $data['days_in_month']) - ( $data['total_days_halfday']*$data['employeeSalary']/ $data['days_in_month']/2);
            
            // $data['currentMonthSalary'] = ( $data['total_days_present']*$data['employeeSalary']/ $data['days_in_month']) - ( $data['total_days_absent']*$data['employeeSalary']/ $data['days_in_month']) - ( $data['total_days_halfday']*$data['employeeSalary']/ $data['days_in_month']/2);

            $joining_date = User::where('id', $request->employee_id)->get('joining_date');
            $joining_dates = substr($joining_date, -13,-6);
        if(($request->year_month) > ($joining_dates))
                {
                    return prepareResult(true,'Record Fatched Successfully' ,$data, 200); 
                }
                return prepareResult(false,'Employee did not Joined on given date' ,null, 500);
                
            } catch (\Throwable $e) {
                Log::error($e);
                return prepareResult(false,'something_went_wrong' ,$e->getMessage(), 500);
            }

        }
    // public function searchEmployeeAttendence(Request $request)
    // {
    //     try {
    //         $query = EmployeeAttendence::select('*')
    //                 ->with('employee_attendence:attendence_id,employee_id,attendence')
    //                 ->orderBy('id', 'desc');

    //         if(!empty($request->id))
    //         {
    //             $query->where('id', $request->id);
    //         }
    //         if(!empty($request->date))
    //         {
    //             $query->where('date', $request->date);
    //         }
    //         if(!empty($request->employee_id))
    //         {
    //             $query->where('employee_id', $request->employee_id);
    //         }
    //         //  // date wise filter from here
    //         //  if(!empty($request->from_date) && !empty($request->end_date))
    //         // {
    //         //     $query->whereDate('employee_attendences.created_at', '>=', $request->from_date)->whereDate('employee_attendences.created_at', '<=', $request->end_date);
    //         // }

    //         if(!empty($request->per_page_record))
    //         {
    //             $perPage = $request->per_page_record;
    //             $page = $request->input('page', 1);
    //             $total = $query->count();
    //             $result = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

    //             $pagination =  [
    //                 'data' => $result,
    //                 'total' => $total,
    //                 'current_page' => $page,
    //                 'per_page' => $perPage,
    //                 'last_page' => ceil($total / $perPage)
    //             ];
    //             $query = $pagination;
    //         }
    //         else
    //         {
    //             $query = $query->get();
    //         }

    //         return response(prepareResult(true, $query, trans('Record Fatched Successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
    //     } 
    //     catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('Error while fatching Records')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
    // }

    // public function store(Request $request)
    // {
    //     $validation = Validator::make($request->all(), [
    //         'date'                    => 'required',
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }

    //     if($request->employee_attendence){

    //         foreach ($request->employee_attendence as $key => $recipe1) {
    //             $currentDate= date("Y-m-d");
    //             // $oldValue1 = AttendenceList::whereDate('created_at', '=',  $currentDate)->where('employee_id', $recipe1['employee_id'])->get('employee_id')->first();
    //             $oldValue1 = AttendenceList::whereDate('date', '=',  $request->date)->where('employee_id', $recipe1['employee_id'])->get('employee_id')->first();
              
    //             $validation = Validator::make($request->all(),[      
    //                 "employee_attendence.*.employee_id"  => $oldValue1 ? 'required|declined:false' : 'required', 
                    
    //              ],
    //              [
    //                  'employee_attendence.*.employee_id.declined' => 'Attendence already exists',
    //              ]
    //          );

             
    //         if ($validation->fails()) {
    //             return response(prepareResult(false, $validation->errors(), trans('translate.validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //         } 
    //         }

    //     }

    //     DB::beginTransaction();
    //     try {
          
    //         $info = new EmployeeAttendence;
    //         $info->date = $request->date;
    //         $info->save();
           
    //        foreach ($request->employee_attendence as $key => $attendence) {
    //            $addAttendence = new AttendenceList;
    //            $addAttendence->attendence_id =  $info->id;
    //            $addAttendence->date = $request->date;
    //            $addAttendence->employee_id = $attendence['employee_id'];
    //            $addAttendence->attendence = $attendence['attendence'];
    //            $addAttendence->save();     
    //        }

    //         DB::commit();
    //         $info['attendence_lists'] = $info->attendenceMethod;
    //         return response()->json(prepareResult(true, $info, trans('Your data has been saved successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         DB::rollback();
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been saved')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
    // }

    // public function update(Request $request, $id)
    // {
    //     $validation = Validator::make($request->all(), [
    //         'date'                    => 'required',
              
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }

    //     DB::beginTransaction();
    //     try {

    //          $info = EmployeeAttendence::find($id);
    //         $info->date = $request->date;
    //         $info->save();

    //         $deletOld = AttendenceList::where('attendence_id', $id)->delete();
    //         foreach ($request->employee_attendence as $key => $attendence) {
    //            $addAttendence = new AttendenceList;
    //            $addAttendence->attendence_id =  $id;
    //            $addAttendence->date = $request->date;
    //            $addAttendence->employee_id = $attendence['employee_id'];
    //            $addAttendence->attendence = $attendence['attendence'];
    //            $addAttendence->save();      
    //        }


    //         DB::commit();
    //         $info['attendence_lists'] = $info->attendenceMethod;
    //         return response()->json(prepareResult(true, $info, trans('Your data has been Updated successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         DB::rollback();
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been Updated')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
    // }

    // public function show($id)
    // {
    //     try {
            
    //         $info = EmployeeAttendence::find($id);
    //         if($info)
    //         {
    //             // return response(prepareResult(false, $info, trans('translate.fetched_records')), config('httpcodes.success'));
    //             return response(prepareResult(true, $info, trans('Record Fatched Successfully')), 200 , ['Result'=>'httpcodes.found']);
    //         }
    //         return response(prepareResult(false, [], trans('Error while fatching Records')),500,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    // }

    // public function employeeID()
    // {
    //     try {

    //         $data = [];
            
    //         $data['employee_attendence']= Employee::select('id as employee_id')->get();
           
    //     //    $data['employeeSalary'] = $employeeData->salary;

    //             // return response(prepareResult(false, $info, trans('translate.fetched_records')), config('httpcodes.success'));
    //             return response(prepareResult(true, $data, trans('Record Fatched Successfully')), 200 , ['Result'=>'httpcodes.found']);
        
    //         return response(prepareResult(false, [], trans('Error while fatching Records')),500,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    // }

    // public function destroy($id)
    // {
    //     try {
            
    //         $info = EmployeeAttendence::find($id);
    //         if($info)
    //         {
    //             $result=$info->delete();
    //             return response(prepareResult(true, $result, trans('Record Id Deleted Successfully')), 200 , ['Result'=>'httpcodes.found']);
    //         }
    //         return response(prepareResult(false, [], trans('Record Id Not Found')),500,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    // }

    // public function dateWiseSearch(Request $request) {
    //     try {
    //     $info = AttendenceList::where('employee_id', $request->employee_id);
    //     if(!empty($request->from_date) && !empty($request->end_date))
    //     {
    //         $info->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
    //     }
    //     elseif(!empty($request->from_date) && empty($request->end_date))
    //     {
    //         $info->whereDate('created_at', '>=', $request->from_date);
    //     }
    //     elseif(empty($request->from_date) && !empty($request->end_date))
    //     {
    //         $info->whereDate('created_at', '<=', $request->end_date);
    //     }
    //     if($info)
    //         {
    //             $result= $info->get();
    //             return response(prepareResult(true, $result, trans('Record Fatched Successfully')), 200 , ['Result'=>'httpcodes.found']);
    //         }
    //         return response(prepareResult(false, [], trans('Error while fatching Records')),500,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    //     // $result= $info->get();
    //     // return $result;
    // }

    // public function monthlyAttendence(Request $request) {
    // try {

    //     $data = [];

    //     // use of template literal while adding date
        
    //     // $data['total_days_present'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',2)->whereDate('created_at', '>=', $request->year_month.'-01' )->whereDate('created_at', '<=', $request->year_month.'-31')->count();

    //     $data['total_days_present'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',2)->whereDate('date', '>=', $request->year_month.'-01' )->whereDate('date', '<=', $request->year_month.'-31')->count();

    //     // $data['total_days_halfday'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',3)->whereDate('created_at', '>=', $request->year_month.'-01' )->whereDate('created_at', '<=', $request->year_month.'-31')->count();

    //     $data['total_days_halfday'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',3)->whereDate('date', '>=', $request->year_month.'-01' )->whereDate('date', '<=', $request->year_month.'-31')->count();

    //     // $data['total_days_absent'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',1)->whereDate('created_at', '>=', $request->year_month.'-01' )->whereDate('created_at', '<=', $request->year_month.'-31')->count();

    //     $data['total_days_absent'] = AttendenceList::where('employee_id', $request->employee_id)->where('attendence',1)->whereDate('date', '>=', $request->year_month.'-01' )->whereDate('date', '<=', $request->year_month.'-31')->count();

    //     $data['days_in_month'] = cal_days_in_month(CAL_GREGORIAN, substr($request->year_month, 5,6), substr($request->year_month, 0,4));
    //     $data['year_month']=$request->year_month;
    //    $employeeData = Employee::where('id', $request->employee_id)->get('salary')->first();
    //     $data['employeeSalary'] = $employeeData->salary;
    //     $data['currentMonthSalary'] = $data['employeeSalary'] - ( $data['total_days_absent']*$data['employeeSalary']/ $data['days_in_month']) - ( $data['total_days_halfday']*$data['employeeSalary']/ $data['days_in_month']/2);
        
    //     $joining_date = Employee::where('id', $request->employee_id)->get('joining_date');
    //     $joining_dates = substr($joining_date, -13,-6);
    // if(($request->year_month) > ($joining_dates))
    //         {
            
    //             return response(prepareResult(true, $data, trans('Record Fatched Successfully')), 200 , ['Result'=>'httpcodes.found']);
    //         }
    //         return response(prepareResult(false, null, trans('Employee did not Joined on given date')),200,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    
   

    // }

}
