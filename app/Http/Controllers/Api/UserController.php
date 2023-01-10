<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class UserController extends Controller
{
    //
    public function searchUser(Request $request)
    {
        try {
            $query = User::select('*')
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
            'email'                      => 'required|email|unique:App\Models\User,email',
            'gender'                   => 'required',
            // 'account_balance'             => 'required|numeric',
            // 'password'              => 'required|confirmed|min:6|max:25',
            'password'              => 'required|min:6|max:25',
            // 'password_confirmation' => 'required'
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
         // file upload format check

        //  $formatCheck = ['doc','docx','png','jpeg','jpg','pdf','svg','mp4','tif','tiff','bmp','gif','eps','raw','jfif','webp','pem','csv'];
        $formatCheck = ['png','jpeg','jpg','bmp','webp'];
         $extension = strtolower($request->image->getClientOriginalExtension());
 
         if(!in_array($extension, $formatCheck))
         {
             return prepareResult(false,'file_not_allowed' ,[], 500);
         } 
           


            $info = new User;
            $info->name = $request->name;
            $info->store_id = $request->store_id;
            $info->email = $request->email;
            $info->password = $request->password;
            $info->role_id = $request->role_id;
            $info->mobile = $request->mobile;
            $info->designation = $request->designation;
            $info->document_type = $request->document_type;
            $info->document_number = $request->document_number;
            $info->address = $request->address;
            $info->joining_date = $request->joining_date;
            $info->birth_date = $request->birth_date;
            $info->gender = $request->gender;
            $info->salary = $request->salary;
            $info->salary_balance = $request->salary_balance;
            $info->account_balance = $request->account_balance;
            if(!empty($request->image))
            {
              $file=$request->image;
            $filename=time().'.'.$file->getClientOriginalExtension();
            // $info->image=env('CDN_DOC_URL').$request->image->move('assets',$filename);
            $info->image=env('CDN_DOC_URL').$request->image->move('assets\user_photos',$filename);
            }
            
            $info->save();

            DB::commit();
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
            
            // $emailCheck = User::where('id',  $id)->get('email')->first();
            $emailCheck = User::find($id);
            // return $emailCheck->email;

        $validation = Validator::make($request->all(), [
            'name'                      => 'required',
            'mobile'                      => 'required|numeric|digits_between:10,10',
            // 'email'                      => 'required|email|unique:App\Models\User,email',
            'gender'                   => 'required',
            'email'                      => $emailCheck->email == $request->email ? 'required' : 'required|email|unique:App\Models\User,email',
            // 'account_balance'             => 'required|numeric',
            // 'password'              => 'required|confirmed|min:6|max:25',
            // 'password'              => 'required|min:6|max:25',
            // 'password_confirmation' => 'required'
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
         // file upload format check

        //  $formatCheck = ['doc','docx','png','jpeg','jpg','pdf','svg','mp4','tif','tiff','bmp','gif','eps','raw','jfif','webp','pem','csv'];
        $formatCheck = ['png','jpeg','jpg','bmp','webp'];
         $extension = strtolower($request->image->getClientOriginalExtension());
 
         if(!in_array($extension, $formatCheck))
         {
             return prepareResult(false,'file_not_allowed' ,[], 500);
         } 

             $info = User::find($id);
             $info->name = $request->name;
            $info->store_id = $request->store_id;
            $info->email = $request->email;
            $info->password = $request->password;
            $info->role_id = $request->role_id;
            $info->mobile = $request->mobile;
            $info->designation = $request->designation;
            $info->document_type = $request->document_type;
            $info->document_number = $request->document_number;
            $info->address = $request->address;
            $info->joining_date = $request->joining_date;
            $info->birth_date = $request->birth_date;
            $info->gender = $request->gender;
            $info->salary = $request->salary;
            $info->salary_balance = $request->salary_balance;
            $info->account_balance = $request->account_balance;
            if(!empty($request->image))
            {
              $file=$request->image;
            $filename=time().'.'.$file->getClientOriginalExtension();
            // $info->image=env('CDN_DOC_URL').$request->image->move('assets',$filename);
            $info->image=env('CDN_DOC_URL').$request->image->move('assets\user_photos',$filename);
            }
            
            $info->save();
   
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
