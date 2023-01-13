<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    //

    //
    public function searchCustomer(Request $request)
    {
        try {
            $query = User::select('*')
                    ->where('role_id', 4)
                    ->orderBy('id', 'desc');

                    
            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->name))
            {
                $query->where('name', $request->name);
            }
            if(!empty($request->designation))
            {
                $query->where('category', 'LIKE', '%'.$request->category.'%');
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
        $validation = Validator::make($request->all(),  [
            'name'                      => 'required',
            'mobile'                      => 'required|numeric|digits_between:10,10',
            // 'email'                      => 'required|email|unique:App\Models\User,email',
            // 'gender'                   => 'required',
            // 'cafe_id'             => 'required|numeric',
            // 'password'              => 'required|confirmed|min:6|max:25',
            // 'password_confirmation' => 'required'
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }  
                 $user = new User;
                //  $user->role_id = $request->role_id;
                $user->role_id = 4;
                //  $user->cafe_id =  $request->cafe_id;
                 $user->name = $request->name;
                 $user->email  = $request->email;
                 $user->mobile = $request->mobile;
                 $user->gender = $request->gender;
                 $user->address = $request->address;
                 $user->account_balance = $request->account_balance;
                 $user->save();
     
           

            DB::commit();
            return prepareResult(true,'Your data has been saved successfully' , $user, 200);
           
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
            
            // $emailCheck = User::where('id',  $id)->get('email')->first();
            $emailCheck = User::find($id);
            // return $emailCheck->email;

        $validation = Validator::make($request->all(), [
            'name'                      => 'required',
            'mobile'                      => 'required|numeric|digits_between:10,10',
            // 'cafe_id'             => 'required|numeric',
            // 'email'     => 'email|required|unique:users,email,'.$id,
            // 'email'                      => 'required|email|unique:App\Models\User,email',
            // 'gender'                   => 'required',
            // 'email'                      => $emailCheck->email == $request->email ? 'required' : 'required|email|unique:App\Models\User,email',
            // 'account_balance'             => 'required|numeric',
            // 'password'              => 'required|confirmed|min:6|max:25',
            // 'password'              => 'required|min:6|max:25',
            // 'password_confirmation' => 'required'
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
  

                $user = User::find($id);
                // $user->role_id = $request->role_id;
                $user->role_id = 4;
                //  $user->cafe_id =  $request->cafe_id;
                 $user->name = $request->name;
                 $user->email  = $request->email;
                 $user->mobile = $request->mobile;
                 $user->gender = $request->gender;
                 $user->address = $request->address;
                 $user->account_balance = $request->account_balance;
                 $user->save();
   
        DB::commit();
        return prepareResult(true,'Your data has been Updated successfully' ,$user, 200);
           
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollback();
            return prepareResult(false,'Your data has not been Updated' ,$e->getMessage(), 500);
            
        }
    }

    public function show($id)
    {
        try {
            
            $info = User::find($id);
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
            
            $info = User::find($id);
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