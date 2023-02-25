<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\CafeSetting;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;


class CafeController extends Controller
{


    //
    public function searchCafe(Request $request)
    {
        try {
            $query = User::select('*')
                    ->where('role_id', 2)
                    // ->with('CafeSetting')
                    ->with('cafeSubscription:id,subscription_type,subscription_charge,subscription_startDate,subscription_endDate,subscription_id','CafeSetting')
                    // ->with('cafeSubscription')
                    ->orderBy('id', 'desc');

                    
            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }
            if(!empty($request->cafe_id))
            {
                $query->where('cafe_id', $request->cafe_id);
            }
            if(!empty($request->mobile))
            {
                $query->where('mobile', 'LIKE', '%'.$request->mobile.'%');
            }
            if(!empty($request->email))
            {
                $query->where('email', 'LIKE', '%'.$request->email.'%');
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
            'contact_person_email'                   => 'required',
            'contact_person_name'                      => 'required',
            'contact_person_phone'                      => 'required',
            // 'password'              => 'required|confirmed|min:6|max:25',
            'password'                   => 'required|min:6|max:25',
            'logo'                       => $request->logo ? 'mimes:jpeg,jpg,png,gif|max:10000' : '',
            // 'logo'                       => 'mimes:jpeg,jpg,png,gif|max:10000'
            // 'password_confirmation' => 'required'
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
          $user = new User;
              //  $user->role_id = $request->role_id;
          $user->role_id = 2;
          $user->uuid = Str::uuid();
        //   $user->uuid = base64_encode(Str::uuid());
          $user->name = $request->name;
          $user->email  = $request->email;
    
              //  $user->password = bcrypt($request->password);
          $user->password =Hash::make($request->password);
          $user->mobile = $request->mobile;
          $user->is_parent = 1;
          $user->address = $request->address;
          $user->subscription_status = $request->subscription_status;
          $user->save();
          $updateCafeId = User::where('id',$user->id)->update(['cafe_id'=> $user->id]);
                
          //Create Store Setting
          $CafeSetting = new CafeSetting();
          $CafeSetting->cafe_id =  $user->id;
          $CafeSetting->name  = $request->name;
          $CafeSetting->description  = $request->description;
          $CafeSetting->website     = $request->website;
          $CafeSetting->address     = $request->address;
          $CafeSetting->subscription_charge = $request->subscription_charge;
          $CafeSetting->subscription_startDate = $request->subscription_startDate;
          $CafeSetting->subscription_endDate = $request->subscription_endDate;
          $CafeSetting->subscription_type = $request->subscription_type;
          $CafeSetting->contact_person_email = $request->contact_person_email; 
          $CafeSetting->contact_person_name = $request->contact_person_name;
          $CafeSetting->contact_person_phone = $request->contact_person_phone;
          if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename=time().'.'.$file->getClientOriginalExtension();
            if ($file->move('assets/user_photos', $filename)) {
                $CafeSetting->logo=env('CDN_DOC_URL').'assets/user_photos/'.$filename.'';
            }
           }
        //  if(!empty($request->logo))
        //   {
        //   $file=$request->logo;
        //    $filename=time().'.'.$file->getClientOriginalExtension();
        //    $CafeSetting->logo=env('CDN_DOC_URL').$request->logo->move('assets/user_photos',$filename);
        //   }
          $CafeSetting->save();

           //----------------saving image--------------------------//
           $user = User::find($user->id);
           $user->image =  $CafeSetting->logo;
           $user->save();

        //    below Unit sometime does not work because someone deletes unit from admin
          //----------------Units--------------------------//
          $units = Unit::where('cafe_id','1')->get();
          foreach($units as $key => $unit){
            $info = new Unit;
            $info->cafe_id =  $user->id;
            $info->name = $unit['name'];
            $info->abbreiation = $unit['abbreiation'];
            $info->minvalue = $unit['minvalue'];
            $info->save();

          }
          

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
            'email'                       => 'email|required|unique:users,email,'.$id,
            'contact_person_email'                   => 'required',
            'contact_person_name'                      => 'required',
            'contact_person_phone'                      => 'required',
            // 'logo'                       => 'mimes:jpeg,jpg,png,gif|max:10000'
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
            //  $user->role_id = $request->role_id;
            $user->uuid = Str::uuid();
            $user->role_id = 2;
             $user->name = $request->name;
             $user->email  = $request->email;
 
            //  $user->password = bcrypt($request->password);
            if(!empty($request->password))
              {
                $user->password =Hash::make($request->password);
              }
            //  $user->password =Hash::make($request->password);
             $user->mobile = $request->mobile;
             $user->is_parent = 1;
             $user->address = $request->address;
            //  $user->subscription_charge = $request->subscription_charge;
            //  $user->subscription_startDate = $request->subscription_startDate;
            //  $user->subscription_endDate = $request->subscription_endDate;
            //  $user->subscription_type = $request->subscription_type;
             $user->subscription_status = $request->subscription_status;
            //  $user->created_by = auth()->id();
             $user->save();

             $CafeSetting = CafeSetting::where('cafe_id', $user->id)->first();
             $CafeSetting->cafe_id =  $user->id;
              $CafeSetting->name  = $request->name;
              $CafeSetting->description  = $request->description;
             //  if(!empty($request->logo))
             //  {
             //      $CafeSetting->logo = $request->logo;
             //  }
              $CafeSetting->website     = $request->website;
              $CafeSetting->address     = $request->address;
              $CafeSetting->subscription_charge = $request->subscription_charge;
              $CafeSetting->subscription_startDate = $request->subscription_startDate;
              $CafeSetting->subscription_endDate = $request->subscription_endDate;
              $CafeSetting->subscription_type = $request->subscription_type;
              $CafeSetting->contact_person_email = $request->contact_person_email; 
              $CafeSetting->contact_person_name = $request->contact_person_name;
              $CafeSetting->contact_person_phone = $request->contact_person_phone;
            //   if(!empty($request->logo))
            //    {
            //    $file=$request->logo;
            //    $filename=time().'.'.$file->getClientOriginalExtension();
            //    // $info->image=env('CDN_DOC_URL').$request->image->move('assets',$filename);
            //    $CafeSetting->logo=env('CDN_DOC_URL').$request->logo->move('assets\user_photos',$filename);
            //   }
            if(!empty($request->logo))
            {
                if(gettype($request->logo) == "string"){
                    $CafeSetting->logo = $request->logo;
                }
                else{
                    if ($request->hasFile('logo')) {
                        $file = $request->file('logo');
                        $filename=time().'.'.$file->getClientOriginalExtension();
                        if ($file->move('assets/user_photos', $filename)) {
                            $CafeSetting->logo=env('CDN_DOC_URL').'assets/user_photos/'.$filename.'';
                        }
                       }
                }
            }
              $CafeSetting->save();

         //----------------saving image--------------------------//
           $user = User::find($id);
           $user->image =  $CafeSetting->logo;
           $user->save();
         
   
        DB::commit();
        return prepareResult(true,'Your data has been Updated successfully' ,$user, 200);
           
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollback();
            return prepareResult(false,'Your data has not been Updated' ,$e->getMessage(), 500);
            
        }
    }

    public function cafeSubscription(Request $request)
    {
        DB::beginTransaction();
        try {
        $validation = Validator::make($request->all(),  [
            'subscription_type'                      => 'required',
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
         
                
          //Create Store Setting
          $CafeSetting = new CafeSetting();
          $CafeSetting->subscription_id =  $request->cafe_id;
          $CafeSetting->subscription_charge = $request->subscription_charge;
          $CafeSetting->subscription_startDate = $request->subscription_startDate;
          $CafeSetting->subscription_endDate = $request->subscription_endDate;
          $CafeSetting->subscription_type = $request->subscription_type;
          $CafeSetting->save();
      

            DB::commit();
            return prepareResult(true,'Your data has been saved successfully' , $CafeSetting, 200);
           
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollback();
            return prepareResult(false,'Your data has not been saved' ,$e->getMessage(), 500);
            
        }
    }

    public function cafeSubscriptionUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            

        $validation = Validator::make($request->all(), [
            'subscription_type'                      => 'required',
            // 'mobile'                      => 'required|numeric|digits_between:10,10',
            // 'email'                       => 'email|required|unique:users,email,'.$id,
            // 'logo'                       => 'mimes:jpeg,jpg,png,gif|max:10000'
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
      

             $CafeSetting = CafeSetting::find($id);
             $CafeSetting->subscription_id =  $request->cafe_id;
             $CafeSetting->subscription_charge = $request->subscription_charge;
             $CafeSetting->subscription_startDate = $request->subscription_startDate;
             $CafeSetting->subscription_endDate = $request->subscription_endDate;
             $CafeSetting->subscription_type = $request->subscription_type;
             $CafeSetting->save();

         
   
        DB::commit();
        return prepareResult(true,'Your data has been Updated successfully' ,$CafeSetting, 200);
           
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

    public function childLogin(Request $request)
    {
    

        $validation = Validator::make($request->all(),  [
            'account_uuid'      => 'required'
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }  

        try {
            $parent_key = null;
            if(empty($request->is_back_to_self_account))
            {
                $parent_key = base64_encode(auth()->user()->uuid);
            }

            $user = User::select('*')
            ->where('uuid', base64_decode($request->account_uuid))
            // ->where('uuid', $request->account_uuid)
            ->withoutGlobalScope('cafe_id')
            ->first();
            // return $user;
            if (!$user)  {
                return prepareResult(false,'user_not_exist' ,[], 500);
                // return response()->json(prepareResult(true, [], trans('translate.user_not_exist'), $this->intime), config('httpcodes.not_found'));
            }
            // $user = $user->makeHidden(['promotional_route','transaction_route','two_waysms_route','voice_sms_route']);

            // if(in_array($user->status, [0,3])) {
            //     return response()->json(prepareResult(true, [], trans('translate.account_is_inactive'), $this->intime), config('httpcodes.unauthorized'));
            // }

            // $accessToken = $user->createToken('authToken')->accessToken;
            // $user['token'] = $accessToken;
            // $user['email'] = $user->email;
            // $user['id'] = $user->id;
            $data = [];

                    
            $data['token'] = $user->createToken('authToken')->accessToken;
            $data['email'] = $user->email;
            $data['id'] = $user->id;
            $data['parent_key'] =  $parent_key;
            // $user['parent_key'] = $parent_key;
            $role   = Role::where('id', $user->role_id)->first();
            $data['permissions']  = $role->permissions()->select('id','se_name', 'group_name','belongs_to')->get();
        
            $userData =[
                   // 'role'=>"admin"$user
                'name'=>$user->name,
                'logo'=>$user->image,
                   'role_id'=>$user->role_id,
            ];
            $data['userData'] =  $userData;

            // $user['permissions'] = $user->permissions()->select('id','name')->orderBy('permission_id', 'ASC')->get();

            return prepareResult(true,'request_successfully_submitted' ,$data, 200);
            // return response()->json(prepareResult(false, $user, trans('translate.request_successfully_submitted'), $this->intime), config('httpcodes.success'));
        } catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'something_went_wrong' ,$e->getMessage(), 500);
        }
    }
    // public function childLogin(Request $request)
    // {
    //     $validation = \Validator::make($request->all(), [
    //         'account_uuid'      => 'required'
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json(prepareResult(true, $validation->messages(), trans('translate.validation_failed'), $this->intime), config('httpcodes.bad_request'));
    //     }

    //     try {
    //         $parent_key = null;
    //         if(empty($request->is_back_to_self_account))
    //         {
    //             $parent_key = base64_encode(auth()->user()->uuid);
    //         }

    //         $user = User::select('*', 'is_show_ratio as sp_operation')
    //         ->where('uuid', base64_decode($request->account_uuid))
    //         ->withoutGlobalScope('parent_id')
    //         ->first();
    //         if (!$user)  {
    //             return response()->json(prepareResult(true, [], trans('translate.user_not_exist'), $this->intime), config('httpcodes.not_found'));
    //         }
    //         $user = $user->makeHidden(['promotional_route','transaction_route','two_waysms_route','voice_sms_route']);

    //         if(in_array($user->status, [0,3])) {
    //             return response()->json(prepareResult(true, [], trans('translate.account_is_inactive'), $this->intime), config('httpcodes.unauthorized'));
    //         }

    //         $accessToken = $user->createToken('authToken')->accessToken;
    //         $user['access_token'] = $accessToken;
    //         $user['permissions'] = $user->permissions()->select('id','name')->orderBy('permission_id', 'ASC')->get();
    //         $user['parent_key'] = $parent_key;
    //         return response()->json(prepareResult(false, $user, trans('translate.request_successfully_submitted'), $this->intime), config('httpcodes.success'));
    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong'), $this->intime), config('httpcodes.internal_server_error'));
    //     }
    // }

    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function cafes(Request $request)
    // {
    //     try {
    //         $query = User::where('role_id','2')->with('CafeSetting');
    //         if(!empty($request->cafe_id))
    //         {
    //             $query->where('cafe_id', $request->cafe_id);
    //         }
    //         if(!empty($request->email))
    //         {
    //             $query->where('email', 'LIKE', '%'.$request->email.'%');
    //         }

    //         if(!empty($request->name))
    //         {
    //             $query->where('name', 'LIKE', '%'.$request->name.'%');
    //         }

    //         if(!empty($request->mobile))
    //         {
    //             $query->where('mobile', 'LIKE', '%'.$request->mobile.'%');
    //         }

            

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

    //         return response(prepareResult(false, $query, trans('translate.fetched_records')), config('httpcodes.success'));
    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    //  public function store(Request $request)
    // {
    //     $validation = \Validator::make($request->all(), [
    //         'name'      => 'required',
    //         'email'     => 'required|email|unique:users,email',
    //         'password'  => 'required|string|min:6',
    //         'mobile_number'    => 'required|min:8|max:15',
    //         'address'  => 'required|string',
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $user = new User;
    //         $user->role_id = $request->role_id;
    //         $user->name = $request->name;
    //         $user->email  = $request->email;

    //         $user->password = bcrypt($request->password);
    //         $user->mobile_number = $request->mobile_number;
    //         $user->is_parent = 1;
    //         $user->address = $request->address;
    //         $user->created_by = auth()->id();
    //         $user->save();
           
    //         //Create Store Setting
    //         $CafeSetting = new CafeSetting();
    //         $CafeSetting->name  = $request->name;
    //         $CafeSetting->description  = $request->description;
    //         if(!empty($request->logo))
    //         {
    //             $CafeSetting->logo = $request->logo;
    //         }
    //         $CafeSetting->website     = $request->website;
    //         $CafeSetting->contact_person_email = $request->contact_person_email; 
    //         $CafeSetting->contact_person_address = $request->contact_person_address;
    //         $CafeSetting->contact_person_phone = $request->contact_person_phone;
    //         $CafeSetting->save();

    //         //Role and permission sync
    //         $role = Role::where('name', 'cafe')->first();
    //         $permissions = $role->permissions->pluck('name');
            
    //         $user->assignRole($role->name);
    //         foreach ($permissions as $key => $permission) {
    //             $user->givePermissionTo($permission);
    //         }

    //         DB::commit();
    //         return response()->json(prepareResult(false, $user, trans('translate.created')),config('httpcodes.created'));
    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         DB::rollback();
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(User $user)
    // {
    //     try {
    //         $userinfo = User::select('*')
    //             ->where('role_id', '2')->with('CafeSetting')
    //             ->find($user->id);
    //         if($userinfo)
    //         {
    //             return response(prepareResult(false, $userinfo, trans('translate.fetched_records')), config('httpcodes.success'));
    //         }
    //         return response(prepareResult(true, [], trans('translate.record_not_found')), config('httpcodes.not_found'));
    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(User $user)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\User  $user
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, User $user)
    // {
    //     $validation = \Validator::make($request->all(), [
    //         'name'      => 'required',
    //         'email'     => 'email|required|unique:users,email,'.$user->id,
    //         'mobile'    => 'required|min:8|max:15',
    //         'address'  => 'required|string',
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
    //     }

    //     DB::beginTransaction();
    //     try {

    //         if(!$user)
    //         {
    //             return response()->json(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
    //         }
        
    //         $user->user_type_id = '2';
    //         $user->name = $request->name;
    //         $user->email  = $request->email;
    //         $user->mobile = $request->mobile;
    //         $user->is_parent = 1;
    //         $user->address = $request->address;
    //         $user->created_by = auth()->id();
    //         $user->save();

    //         //delete old role and permissions
    //         DB::table('model_has_roles')->where('model_id', $user->id)->delete();
    //         DB::table('model_has_permissions')->where('model_id', $user->id)->delete();

    //         //Role and permission sync
    //         $role = Role::where('name', 'store')->first();
    //         $permissions = $role->permissions->pluck('name');
            
    //         $user->assignRole($role->name);
    //         foreach ($permissions as $key => $permission) {
    //             $user->givePermissionTo($permission);
    //         }
           
    //         //Create Store Setting
    //         if(CafeSetting::where('cafe_id', $user->id)->count()>0)
    //         {
    //             $CafeSetting = CafeSetting::where('cafe_id', $user->id)->first();
    //         }
    //         else
    //         {
    //             $CafeSetting = new CafeSetting;
    //         }
    //         $CafeSetting->name  = $request->name;
    //         $CafeSetting->description  = $request->description;
    //         if(!empty($request->logo))
    //         {
    //             $CafeSetting->logo = $request->logo;
    //         }
    //         $CafeSetting->website     = $request->website;
    //         $CafeSetting->contact_person_email = $request->contact_person_email; 
    //         $CafeSetting->contact_person_address = $request->contact_person_address;
    //         $CafeSetting->contact_person_phone = $request->contact_person_phone;
    //         $CafeSetting->save();



            
    //         DB::commit();
    //         return response()->json(prepareResult(false, $user, trans('translate.updated')),config('httpcodes.success'));
    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         DB::rollback();
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(User $user)
    // {
    //     try {
    //         if($user->role_id=='1')
    //         {
    //             return response(prepareResult(true, [], trans('translate.record_not_found')), config('httpcodes.not_found'));
    //         }
    //         $user_id = $user->id;
    //         $user->email = $user->email;
    //         $user->status = false;
    //         $user->save();

    //         $isDeleted = $user->delete();
           
    //         return response()->json(prepareResult(false, [], trans('translate.deleted')), config('httpcodes.success'));
    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }
}
