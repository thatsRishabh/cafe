<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    //
    public function searchEmployee(Request $request)
    {
        try {
            $query = User::select('*')
                    // ->where('role_id', 3)
                    ->whereIn('role_id', [3, 5])
                    ->orderBy('id', 'desc');

            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->role_id))
            {
                $query->where('role_id', $request->role_id);
            }
            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }

            if(!empty($request->email))
            {
                $query->where('email', 'LIKE', '%'.$request->email.'%');
            }

            if(!empty($request->mobile))
            {
                $query->where('mobile', 'LIKE', '%'.$request->mobile.'%');
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
            'mobile'                      => 'required|numeric|digits_between:10,10|unique:App\Models\User,mobile',
            'email'                      => 'required|email|unique:App\Models\User,email',
            'document_number'                      => 'required|unique:App\Models\User,document_number',
            // 'image'                       => 'mimes:jpeg,jpg,png,gif|max:10000',
            'image'                       => $request->image ? 'mimes:jpeg,jpg,png,gif|max:10000' : '',
            // 'gender'                   => 'required',
            // 'account_balance'             => 'required|numeric',
            // 'password'              => 'required|confirmed|min:6|max:25',
            'password'              => 'required|min:6|max:25',
            // 'password_confirmation' => 'required'
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }  
       

                $user = new User;
                $user->uuid = Str::uuid();
                 $user->role_id = $request->role_id;
                 if($request->role_id == 5)
                  {
                  $user->cafe_id =  1;
                  }
                 //$user->cafe_id =  $request->cafe_id;
                 $user->name = $request->name;
                 $user->email  = $request->email;
                //  $user->password = bcrypt($request->password);
                 $user->password =Hash::make($request->password);
                 $user->mobile = $request->mobile;
                 $user->designation = $request->designation;
                 $user->document_type = $request->document_type;
                 $user->document_number = $request->document_number;
                 $user->joining_date = $request->joining_date;
                 $user->birth_date = $request->birth_date;
                 $user->gender = $request->gender;
                 $user->salary = $request->salary;
                 $user->salary_balance = $request->salary_balance;
            
                 $user->address = $request->address;
                
                //  if(!empty($request->image))
                //   {
                //   $file=$request->image;
                //   $filename=time().'.'.$file->getClientOriginalExtension();
                //   // $info->image=env('CDN_DOC_URL').$request->image->move('assets',$filename);
                //   $user->image=env('CDN_DOC_URL').$request->image->move('assets\user_photos',$filename);
                //   }
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $filename=time().'.'.$file->getClientOriginalExtension();
                    if ($file->move('assets/user_photos', $filename)) {
                        $user->image=env('CDN_DOC_URL').'assets/user_photos/'.$filename.'';
                    }
                   }
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
            'mobile'                      => 'required|numeric|digits_between:10,10|unique:users,mobile,'.$id,
            'email'                        => 'email|required|unique:users,email,'.$id,
            'document_number'                      => 'required|unique:users,document_number,'.$id,
            // 'email'                      => 'required|email|unique:App\Models\User,email',
            // 'image'                       => $request->image ? 'mimes:jpeg,jpg,png,gif|max:10000' : '',
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
             $user->uuid = Str::uuid();
             $user->role_id = $request->role_id;
             if($request->role_id == 5)
             {
             $user->cafe_id =  1;
             }
             $user->name = $request->name;
             $user->email  = $request->email;
            //  $user->password = bcrypt($request->password);
            if(!empty($request->password))
            {
              $user->password =Hash::make($request->password);
            }
            //  $user->password =Hash::make($request->password);
             $user->mobile = $request->mobile;
             $user->designation = $request->designation;
             $user->document_type = $request->document_type;
             $user->document_number = $request->document_number;
             $user->joining_date = $request->joining_date;
             $user->birth_date = $request->birth_date;
             $user->gender = $request->gender;
             $user->salary = $request->salary;
             $user->salary_balance = $request->salary_balance;
        
             $user->address = $request->address;
            
            //  if(!empty($request->image))
            //   {
            //   $file=$request->image;
            //   $filename=time().'.'.$file->getClientOriginalExtension();
            //   // $info->image=env('CDN_DOC_URL').$request->image->move('assets',$filename);
            //   $user->image=env('CDN_DOC_URL').$request->image->move('assets\user_photos',$filename);
            //   }
            if(!empty($request->image))
            {
                if(gettype($request->image) == "string"){
                    $user->image = $request->image;
                }
                else{
                    if ($request->hasFile('image')) {
                        $file = $request->file('image');
                        $filename=time().'.'.$file->getClientOriginalExtension();
                        if ($file->move('assets/user_photos', $filename)) {
                            $user->image=env('CDN_DOC_URL').'assets/user_photos/'.$filename.'';
                        }
                       }
                }
            }
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
