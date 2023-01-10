<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\CafeSetting;

class CafeController extends Controller
{
    //
    public function searchCafeSetting(Request $request)
    {
        try {
            $query = CafeSetting::select('*')
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
            'cafe_id'                      => 'required|numeric',
            'email'                      => 'required|email|unique:App\Models\CafeSetting,email',
            'address'                   => 'required',
           
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
         // file upload format check

        //  $formatCheck = ['doc','docx','png','jpeg','jpg','pdf','svg','mp4','tif','tiff','bmp','gif','eps','raw','jfif','webp','pem','csv'];
        $formatCheck = ['png','jpeg','jpg','bmp','webp'];
         $extension = strtolower($request->logo->getClientOriginalExtension());
 
         if(!in_array($extension, $formatCheck))
         {
             return prepareResult(false,'file_not_allowed' ,[], 500);
         } 
       

            $info = new CafeSetting;
            $info->name = $request->name;
            $info->cafe_id = $request->cafe_id;
            $info->email = $request->email;
            $info->website = $request->website;
            $info->contact_person_name = $request->contact_person_name;
            $info->contact_person_email = $request->contact_person_email;
            $info->contact_person_phone = $request->contact_person_phone;
            $info->address = $request->address;
            $info->description = $request->description;
    
            if(!empty($request->logo))
            {
              $file=$request->logo;
            $filename=time().'.'.$file->getClientOriginalExtension();
            // $info->image=env('CDN_DOC_URL').$request->image->move('assets',$filename);
            $info->logo=env('CDN_DOC_URL').$request->logo->move('assets\logo',$filename);
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
            $emailCheck = CafeSetting::find($id);
            // return $emailCheck->email;

        $validation = Validator::make($request->all(), [
            'name'                      => 'required',
            'address'                      => 'required',
            'cafe_id'                      => 'required|numeric',
            'email'                      => $emailCheck->email == $request->email ? 'required' : 'required|email|unique:App\Models\CafeSetting,email',
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
         $extension = strtolower($request->logo->getClientOriginalExtension());
 
         if(!in_array($extension, $formatCheck))
         {
             return prepareResult(false,'file_not_allowed' ,[], 500);
         } 

             $info = CafeSetting::find($id);
             $info->name = $request->name;
             $info->cafe_id = $request->cafe_id;
             $info->email = $request->email;
             $info->website = $request->website;
             $info->contact_person_name = $request->contact_person_name;
             $info->contact_person_email = $request->contact_person_email;
             $info->contact_person_phone = $request->contact_person_phone;
             $info->address = $request->address;
             $info->description = $request->description;
     
             if(!empty($request->logo))
             {
               $file=$request->logo;
             $filename=time().'.'.$file->getClientOriginalExtension();
             // $info->image=env('CDN_DOC_URL').$request->image->move('assets',$filename);
             $info->logo=env('CDN_DOC_URL').$request->logo->move('assets\logo',$filename);
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
            
            $info = CafeSetting::find($id);
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
            
            $info = CafeSetting::find($id);
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
