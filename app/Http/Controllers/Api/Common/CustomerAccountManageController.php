<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\CustomerAccountManage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Models\User;


class CustomerAccountManageController extends Controller
{

    public function searchCustomerAccount(Request $request)
    {
        try {
            $query = CustomerAccountManage::select('*')
                    ->with('customerName:id,name,role_id')
                    ->orderBy('id', 'desc');
                   

                    // below query is to search inside join function 
                    $name = $request->name;
                    if(!empty($request->name))
                    {
                        $query->whereHas('customerName',function ($query) use ($name) {
                            $query->Where('name', 'LIKE', "%{$name}%");
                        });    
                    }        
                    if(!empty($request->id))
                    {
                        $query->where('id', $request->id);
                    }
                    if(!empty($request->transaction_type))
                    {
                        $query->where('transaction_type', $request->transaction_type);
                    }
                    if(!empty($request->customer_id))
                    {
                        $query->where('customer_id', $request->customer_id);
                    }
                    if(!empty($request->account_status))
                    {
                        $query->where('account_status', $request->account_status);
                    }
                    // date wise filter from here
                     if(!empty($request->from_date) && !empty($request->end_date))
                    {
                        $query->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
                    }
        
                    if(!empty($request->from_date) && !empty($request->end_date) && !empty($request->customer_id))
                    {
                        $query->where('customer_id', $request->customer_id)->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
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


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
        
                        // 'change_in_balance'                   => 'nullable|numeric',
                        // 'transaction_type'                    => 'required',
                        'customer_id'                         => 'required|numeric',
                       
                    ]);
            
        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
           $old = User::where('id', $request->customer_id)->get('account_balance')->first();

            
            $info = new CustomerAccountManage;
            $info->customer_id = $request->customer_id;
            // $info->unit_id = $request->unit_id;

            // storing old stock from product infos stock table
            $info->previous_balance = $old->account_balance;
            $info->sale = $request->sale;
            $info->payment_received = $request->payment_received ;

            // stock in/out calculation
            // $info->new_balance = strtolower($request->transaction_type) == "credit" 
            // ? $old->account_balance + $request->change_in_balance 
            // : $old->account_balance - $request->change_in_balance;

            $info->new_balance = $request->payment_received >= $request->sale
            ? $old->account_balance + ($request->payment_received - $request->sale)
            : $old->account_balance - ($request->sale  - $request->payment_received);
           
            // $info->transaction_type = $request->transaction_type;
            $info->mode_of_transaction = $request->mode_of_transaction;
            // $info->account_status = $request->account_status;
            $info->save();

            // updating the Customer table as well
            $updateBalance = User::find( $request->customer_id);
            $updateBalance->account_balance = $info->new_balance;
            $updateBalance->save();

            DB::commit();
            // $info['product_menus'] = $info->halfPrice;
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
            
                    // 'change_in_balance'                   => 'nullable|numeric',
                    // 'transaction_type'                    => 'required',
                    'customer_id'                         => 'required|numeric',
                
                ]);
           if ($validation->fails()) {
                        return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
                       
           }    

            $old = User::where('id', $request->customer_id)->get('account_balance')->first();

            
            $info = CustomerAccountManage::find($id);
            $info->customer_id = $request->customer_id;


            // storing old stock from product infos stock table
            // $info->previous_balance = $old->account_balance;
            $info->previous_balance = $info->previous_balance;
            // $info->change_in_balance = $request->change_in_balance;

            $info->sale = $request->sale;
            $info->payment_received = $request->payment_received ;

            // stock in/out calculation

            // $info->new_balance = strtolower($request->transaction_type) == "credit" 
            // ? $info->previous_balance + $request->change_in_balance 
            // : $info->previous_balance - $request->change_in_balance;

            $info->new_balance = $request->payment_received >= $request->sale
            ? $info->previous_balance + ($request->payment_received - $request->sale)
            : $info->previous_balance - ($request->sale  - $request->payment_received);
           
            // $info->transaction_type = $request->transaction_type;
            $info->mode_of_transaction = $request->mode_of_transaction;
            // $info->account_status = $request->account_status;
            $info->save();

            // updating the productinfo table as well
            $updateBalance = User::find( $request->customer_id);
            $updateBalance->account_balance = $info->new_balance;
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
            
            $info = CustomerAccountManage::find($id);
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
            
            $info = CustomerAccountManage::find($id);
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

}
