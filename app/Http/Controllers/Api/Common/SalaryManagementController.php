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
    public function searchSalary(Request $request)
    {
        try {

            $query = SalaryManagement::select('*')
                    // ->join('customers', 'customer_account_manages.customer_id', '=', 'customers.id')
                    // ->select('customer_account_manages.*','customers.name as customers_name')
                    ->with('employee:id,name,salary')
                    // ->with('employeeName')
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
        
                    // elseif(!empty($request->from_date) && empty($request->end_date))
                    // {
                    //     $query->where('customer_id', $request->customer_id)->whereDate('customer_account_manages.created_at', '>=', $request->from_date);
                    // }
                    // elseif(empty($request->from_date) && !empty($request->end_date))
                    // {
                    //     $query->where('customer_id', $request->customer_id)->whereDate('customer_account_manages.created_at', '<=', $request->end_date);
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
        
            'paid_amount'                   => 'required|numeric',
            'employee_id'                         => 'required|numeric',
           
        ]);
        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
        $old = User::where('id', $request->employee_id)->get('salary_balance')->first();

            
            $info = new SalaryManagement;
            $info->employee_id = $request->employee_id;
            // $info->unit_id = $request->unit_id;

            // storing old salary from employee table
            $info->previous_balance = $old->salary_balance;
            $info->paid_amount = $request->paid_amount;

            // stock in/out calculation
            $info->new_balance =  $old->salary_balance - $request->paid_amount;
            $info->save();

            // updating the productinfo table as well
            $updateBalance = User::find( $request->employee_id);
            $updateBalance->salary_balance =  $info->new_balance;
            $updateBalance->save();

            DB::commit();
            // $info['product_menus'] = $info->halfPrice;
            // $info['salary_management'] = $info->employee;
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
        
                'paid_amount'                   => 'required|numeric',
                'employee_id'                         => 'required|numeric',
            
            ]);
           if ($validation->fails()) {
                        return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
                       
           }    

         $old = User::where('id', $request->employee_id)->get('salary_balance')->first();

            
            $info = SalaryManagement::find($id);
            $info->employee_id = $request->employee_id;
            // $info->unit_id = $request->unit_id;

            // storing old stock from product infos stock table
            // $info->previous_balance = $old->account_balance;
            $info->previous_balance = $info->previous_balance;
            $info->paid_amount = $request->paid_amount;

    
            $info->new_balance = $info->previous_balance - $request->paid_amount;
           
            // $info->account_status = $request->account_status;
            $info->save();

            // updating the productinfo table as well
            $updateBalance = User::find( $request->employee_id);
            $updateBalance->salary_balance =  $info->new_balance;
            $updateBalance->save();

            DB::commit();
     
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
            
            $info = SalaryManagement::find($id);
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
            
            $info = SalaryManagement::find($id);
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
    // public function searchSalary(Request $request)
    // {
    //     try {
    //         $query = SalaryManagement::select('*')
    //         // ->join('customers', 'customer_account_manages.customer_id', '=', 'customers.id')
    //         // ->select('customer_account_manages.*','customers.name as customers_name')
    //         ->with('employee:id,name,salary')
    //         // ->with('employeeName')
    //         ->orderBy('id', 'desc');

    //         if(!empty($request->id))
    //         {
    //             $query->where('id', $request->id);
    //         }
    //         if(!empty($request->employee_id))
    //         {
    //             $query->where('employee_id', $request->employee_id);
    //         }

    //         // date wise filter from here
    //          if(!empty($request->from_date) && !empty($request->end_date))
    //         {
    //             $query->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
    //         }

    //         if(!empty($request->from_date) && !empty($request->end_date) && !empty($request->employee_id))
    //         {
    //             $query->where('employee_id', $request->employee_id)->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
    //         }

    //         // elseif(!empty($request->from_date) && empty($request->end_date))
    //         // {
    //         //     $query->where('customer_id', $request->customer_id)->whereDate('customer_account_manages.created_at', '>=', $request->from_date);
    //         // }
    //         // elseif(empty($request->from_date) && !empty($request->end_date))
    //         // {
    //         //     $query->where('customer_id', $request->customer_id)->whereDate('customer_account_manages.created_at', '<=', $request->end_date);
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

    //         return response(prepareResult(true, $query, trans('Record Featched Successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
    //     } 
    //     catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('Error while featching Records')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
    // }

    // public function store(Request $request)
    // {
    //     $validation = Validator::make($request->all(), [
        
    //         'paid_amount'                   => 'required|numeric',
    //         'employee_id'                         => 'required|numeric',
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     } 

    //     DB::beginTransaction();
    //     try {
          
    //         $old = Employee::where('employees.id', $request->employee_id)->get('salary_balance')->first();

            
    //         $info = new SalaryManagement;
    //         $info->employee_id = $request->employee_id;
    //         // $info->unit_id = $request->unit_id;

    //         // storing old salary from employee table
    //         $info->previous_balance = $old->salary_balance;
    //         $info->paid_amount = $request->paid_amount;

    //         // stock in/out calculation
    //         $info->new_balance =  $old->salary_balance - $request->paid_amount;
    //         $info->save();

    //         // updating the productinfo table as well
    //         $updateBalance = Employee::find( $request->employee_id);
    //         $updateBalance->salary_balance =  $info->new_balance;
    //         $updateBalance->save();

    //         DB::commit();
    //         // $info['product_menus'] = $info->halfPrice;
    //         // $info['salary_management'] = $info->employee;
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
      
    //         'paid_amount'                   => 'required|numeric',
    //         'employee_id'                         => 'required|numeric',
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $old = Employee::where('employees.id', $request->employee_id)->get('salary_balance')->first();

            
    //         $info = SalaryManagement::find($id);
    //         $info->employee_id = $request->employee_id;
    //         // $info->unit_id = $request->unit_id;

    //         // storing old stock from product infos stock table
    //         // $info->previous_balance = $old->account_balance;
    //         $info->previous_balance = $info->previous_balance;
    //         $info->paid_amount = $request->paid_amount;

    
    //         $info->new_balance = $info->previous_balance - $request->paid_amount;
           
    //         // $info->account_status = $request->account_status;
    //         $info->save();

    //         // updating the productinfo table as well
    //         $updateBalance = Employee::find( $request->employee_id);
    //         $updateBalance->salary_balance =  $info->new_balance;
    //         $updateBalance->save();

    //         DB::commit();
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
            
    //         $info = SalaryManagement::find($id);
    //         if($info)
    //         {
    //             // return response(prepareResult(false, $info, trans('translate.fetched_records')), config('httpcodes.success'));
    //             return response(prepareResult(true, $info, trans('Record Featched Successfully')), 200 , ['Result'=>'httpcodes.found']);
    //         }
    //         return response(prepareResult(false, [], trans('Error while featching Records')),500,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    // }

    // public function destroy($id)
    // {
    //     try {
            
    //         $info = SalaryManagement::find($id);
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
}
