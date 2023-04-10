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
	public function cafes(Request $request)
	{
		try {
			$query = User::select('*')
			->where('role_id', 2)
			->with('cafeSubscription:id,subscription_type,subscription_charge,subscription_startDate,subscription_endDate,subscription_id','CafeSetting')
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
			if(!empty($request->category))
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
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
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
				'password'                   => 'required|min:6|max:25',
				'image'                       => $request->image ? 'mimes:jpeg,jpg,png,gif|max:10000' : '',
			]);

			if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		}      
			$user = new User;
			$user->role_id = 2;
			$user->uuid = Str::uuid();
			$user->name = $request->name;
			$user->email  = $request->email;
			$user->password = Hash::make($request->password);
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
			if ($request->hasFile('image')) {
				$file = $request->file('image');
				$filename = time().'.'.$file->getClientOriginalExtension();
				if ($file->move('assets/user_photos', $filename)) {
					$CafeSetting->image = env('CDN_DOC_URL').'assets/user_photos/'.$filename.'';
				}
			}
			$CafeSetting->save();

           //----------------saving image--------------------------//
			$user = User::find($user->id);
			$user->image =  $CafeSetting->image;
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
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}


	public function update(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'name'  => 'required',
			'mobile' => 'required|numeric|digits_between:10,10',
			'email' => 'email|required|unique:users,email,'.$id,
			'contact_person_email' => 'required',
			'contact_person_name' => 'required',
			'contact_person_phone' => 'required',
		]);

		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

		}
		DB::beginTransaction();
		try {

			$user = User::find($id);
			if (empty($user)) {
				return prepareResult(false,'user not found' ,[], 500);
			}
			$user->uuid = Str::uuid();
			$user->role_id = 2;
			$user->name = $request->name;
			$user->email  = $request->email;
			if(!empty($request->password))
			{
				$user->password = Hash::make($request->password);
			}
			$user->mobile = $request->mobile;
			$user->is_parent = 1;
			$user->address = $request->address;
			$user->subscription_status = $request->subscription_status;
			$user->save();

			$CafeSetting = CafeSetting::where('cafe_id', $user->id)->first();
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

			if(!empty($request->image))
			{
				if(gettype($request->image) == "string"){
					$CafeSetting->image = $request->image;
				}
				else{
					if ($request->hasFile('image')) {
						$file = $request->file('image');
						$filename=time().'.'.$file->getClientOriginalExtension();
						if ($file->move('assets/user_photos', $filename)) {
							$CafeSetting->image=env('CDN_DOC_URL').'assets/user_photos/'.$filename.'';
						}
					}
				}
			}
			$CafeSetting->save();

         //----------------saving image--------------------------//
			$user = User::find($id);
			$user->image =  $CafeSetting->image;
			$user->save();


			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$user, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function cafeSubscription(Request $request)
	{
		DB::beginTransaction();
		try {
			$validation = Validator::make($request->all(),  [
				'subscription_type' => 'required',

			]);

			if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

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
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function cafeSubscriptionUpdate(Request $request, $id)
	{
		$validation = Validator::make($request->all(), [
			'subscription_type' => 'required',
		]);

		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);
		} 
		DB::beginTransaction();
		try {  

			$cafeSetting = CafeSetting::find($id);
			if (empty($cafeSetting)) {
				return prepareResult(false,'Record not found' ,[], 500);
			}
			$cafeSetting->subscription_id =  $request->cafe_id;
			$cafeSetting->subscription_charge = $request->subscription_charge;
			$cafeSetting->subscription_startDate = $request->subscription_startDate;
			$cafeSetting->subscription_endDate = $request->subscription_endDate;
			$cafeSetting->subscription_type = $request->subscription_type;
			$cafeSetting->save();
			DB::commit();
			return prepareResult(true,'Your data has been Updated successfully' ,$cafeSetting, 200);

		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
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
			return prepareResult(false,'Record not found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function destroy($id)
	{
		try {
			$info = User::find($id);
			if($info)
			{
				$result=$info->delete();
				return prepareResult(true,'Record Deleted Successfully' ,$result, 200); 
			}
			return prepareResult(false,'Record Not Found' ,[], 500);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}

	public function childLogin(Request $request)
	{
		$validation = Validator::make($request->all(),  [
			'account_uuid'      => 'required'
		]);

		if ($validation->fails()) {
			return prepareResult(false,$validation->errors()->first() ,$validation->errors(), 500);

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
			if (!$user)  {
				return prepareResult(false,'Record not found' ,[], 500);
			}
			$user['token'] = $user->createToken('authToken')->accessToken;
			$user['parent_key'] =  $parent_key;
			$role   = Role::where('id', $user->role_id)->first();
			$user['permissions']  = $role->permissions()->select('id','se_name', 'group_name','belongs_to')->get();

			return prepareResult(true,'request_successfully_submitted' ,$user, 200);
		} catch (\Throwable $e) {
			Log::error($e);
			return prepareResult(false,'Oops! Something went wrong.' ,$e->getMessage(), 500);
		}
	}
}
