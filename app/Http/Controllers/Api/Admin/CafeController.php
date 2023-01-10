<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CafeSetting;
use Illuminate\Http\Request;

class CafeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cafes(Request $request)
    {
        try {
            $query = User::where('role_id','2')->with('CafeSetting');
            if(!empty($request->cafe_id))
            {
                $query->where('cafe_id', $request->cafe_id);
            }
            if(!empty($request->email))
            {
                $query->where('email', 'LIKE', '%'.$request->email.'%');
            }

            if(!empty($request->name))
            {
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }

            if(!empty($request->mobile))
            {
                $query->where('mobile', 'LIKE', '%'.$request->mobile.'%');
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

            return response(prepareResult(false, $query, trans('translate.fetched_records')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'mobile_number'    => 'required|min:8|max:15',
            'address'  => 'required|string',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {
            $user = new User;
            $user->role_id = $request->role_id;
            $user->name = $request->name;
            $user->email  = $request->email;

            $user->password = bcrypt($request->password);
            $user->mobile_number = $request->mobile_number;
            $user->is_parent = 1;
            $user->address = $request->address;
            $user->created_by = auth()->id();
            $user->save();
           
            //Create Store Setting
            $CafeSetting = new CafeSetting();
            $CafeSetting->name  = $request->name;
            $CafeSetting->description  = $request->description;
            if(!empty($request->logo))
            {
                $CafeSetting->logo = $request->logo;
            }
            $CafeSetting->website     = $request->website;
            $CafeSetting->contact_person_email = $request->contact_person_email; 
            $CafeSetting->contact_person_address = $request->contact_person_address;
            $CafeSetting->contact_person_phone = $request->contact_person_phone;
            $CafeSetting->save();

            //Role and permission sync
            $role = Role::where('name', 'cafe')->first();
            $permissions = $role->permissions->pluck('name');
            
            $user->assignRole($role->name);
            foreach ($permissions as $key => $permission) {
                $user->givePermissionTo($permission);
            }

            DB::commit();
            return response()->json(prepareResult(false, $user, trans('translate.created')),config('httpcodes.created'));
        } catch (\Throwable $e) {
            \Log::error($e);
            DB::rollback();
            return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        try {
            $userinfo = User::select('*')
                ->where('role_id', '2')->with('CafeSetting')
                ->find($user->id);
            if($userinfo)
            {
                return response(prepareResult(false, $userinfo, trans('translate.fetched_records')), config('httpcodes.success'));
            }
            return response(prepareResult(true, [], trans('translate.record_not_found')), config('httpcodes.not_found'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validation = \Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'email|required|unique:users,email,'.$user->id,
            'mobile'    => 'required|min:8|max:15',
            'address'  => 'required|string',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
        }

        DB::beginTransaction();
        try {

            if(!$user)
            {
                return response()->json(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
            }
        
            $user->user_type_id = '2';
            $user->name = $request->name;
            $user->email  = $request->email;
            $user->mobile = $request->mobile;
            $user->is_parent = 1;
            $user->address = $request->address;
            $user->created_by = auth()->id();
            $user->save();

            //delete old role and permissions
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            DB::table('model_has_permissions')->where('model_id', $user->id)->delete();

            //Role and permission sync
            $role = Role::where('name', 'store')->first();
            $permissions = $role->permissions->pluck('name');
            
            $user->assignRole($role->name);
            foreach ($permissions as $key => $permission) {
                $user->givePermissionTo($permission);
            }
           
            //Create Store Setting
            if(CafeSetting::where('cafe_id', $user->id)->count()>0)
            {
                $CafeSetting = CafeSetting::where('cafe_id', $user->id)->first();
            }
            else
            {
                $CafeSetting = new CafeSetting;
            }
            $CafeSetting->name  = $request->name;
            $CafeSetting->description  = $request->description;
            if(!empty($request->logo))
            {
                $CafeSetting->logo = $request->logo;
            }
            $CafeSetting->website     = $request->website;
            $CafeSetting->contact_person_email = $request->contact_person_email; 
            $CafeSetting->contact_person_address = $request->contact_person_address;
            $CafeSetting->contact_person_phone = $request->contact_person_phone;
            $CafeSetting->save();



            
            DB::commit();
            return response()->json(prepareResult(false, $user, trans('translate.updated')),config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            DB::rollback();
            return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        try {
            if($user->role_id=='1')
            {
                return response(prepareResult(true, [], trans('translate.record_not_found')), config('httpcodes.not_found'));
            }
            $user_id = $user->id;
            $user->email = $user->email;
            $user->status = false;
            $user->save();

            $isDeleted = $user->delete();
           
            return response()->json(prepareResult(false, [], trans('translate.deleted')), config('httpcodes.success'));
        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
        }
    }
}