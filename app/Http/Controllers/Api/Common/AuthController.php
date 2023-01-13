<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
// use Auth;
use DB;
use Exception;
use Mail;
// use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    public function login(Request $request)
    {
       
       
        try {
            // $user = User::where('email',$request->email)->first();
            $user = User::select('*')->where('email', $request->email)->withoutGlobalScope('cafe_id')->first();
            // $employeeInfo = Employee::where('email',$request->email)->first();

            // return $user;
            if (!empty($user)) {

                $userRoleID = User::where('email',$request->email)->get('role_id')->first();

                $validation = Validator::make($request->all(),  [
                   
                    'email'                      => 'required|email',
                    'password'                  => 'required',
                   
                ],
                [
                    'entry_mode.declined' => 'Login not allowed to this user'
                ]);
        
                if ($validation->fails()) {
                    return  prepareResult(false,'validation failed' ,[], 500);
                }
                
                if (Hash::check($request->password, $user->password)) {

                    $data = [];

                    
                    $data['token'] = $user->createToken('authToken')->accessToken;
                    $data['email'] = $request->email;
                    $data['id'] = $user->id;
                    // $permissionData[] =[
                    //     'action'=>"dashboard",
                    //     'name'=>"dashboard-view",
                    // ];
                    // $data['permissions'] =  $permissionData;
                    $role   = Role::where('id', $user->role_id)->first();
                    $data['permissions']  = $role->permissions()->select('id','se_name', 'group_name','belongs_to')->get();
                    $userData =[
                        // 'role'=>"admin"$user
                        'role_id'=>$user->role_id
                    ];
                    $data['user'] =  $userData;
                    // $data['employeeInfo'] =  $employeeInfo;
                    // $token = $user->createToken('authToken')->accessToken;
                   
                    // $token = auth()->user()->createToken('authToken')->accessToken;

                    // $info = "Hello world";
                    // return "Hello world";
                    // return prepareResult(true,'logged in successfully' ,$data, 200);
                    // } else {
                    //     return prepareResult(false,'wrong email or password' ,[], 500);
                    return prepareResult(true,'logged in successfully' ,$data, 200);

                    } else {
                        // return response(prepareResult(false, [], trans('message_wrong_password')), 500,  ['Result'=>'message_wrong_password']);
                        prepareResult(false,'wrong email or password' ,[], 500);
                } 
             } else {
                return prepareResult(false,'user not found' ,[], 500);    
            }
            
         } catch (\Throwable $e) {
                Log::error($e);
                return prepareResult(false,$e->getMessage() ,[], 500); 
            }
   }

   public function logout(Request $request)
   {
    if (Auth::check()) 
    {
        try
        {
            $token = Auth::user()->token();
            $token->revoke();
            auth('api')->user()->tokens->each(function ($token, $key) {
                $token->delete();
            });
            return prepareResult(true,'logged out successfully' ,[], 200); 
        }
        catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'internal_server_error' ,[], 500);
        }
    }

    //    $user = getUser();
    //    if (!is_object($user)) {
    //        return prepareResult(false,'user not found' ,[], 500); 
    //    }
    //    if(Auth::check()) {
    //            $tokenId = $request->bearerToken();
    //            Auth::user()->token()->revoke();
                
               
               // $tokenRepository = app(TokenRepository::class);
               // $refreshTokenRepository = app(RefreshTokenRepository::class);
               
               // // Revoke an access token...
               // $tokenRepository->revokeAccessToken($tokenId);
               
               // // Revoke all of the token's refresh tokens...
               // $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);

    //        return prepareResult(true,'logged out successfully' ,[], 200); 
    //    }else{
    //        return prepareResult(false,'internal_server_error' ,[], 500);     
    //    }
    //    // return  $request->bearerToken();
    //    return $user;

   }

    // public function login(Request $request)
    // {
    //     $validation = \Validator::make($request->all(),[ 
    //         'email'     => 'required',
    //         'password'  => 'required',
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
    //     }

    //     try {
    //         $email = $request->email;
    //         $user = User::select('*')->where('email', $email)->withoutGlobalScope('store_id')->first();
    //         if (!$user)  {
    //             return response()->json(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
    //         }

    //         if(in_array($user->status, [0,3])) {
    //             return response()->json(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
    //         }

    //         if(Hash::check($request->password, $user->password)) {
    //             $accessToken = $user->createToken('authToken')->accessToken;
    //             $user['access_token'] = $accessToken;
    //             //$user['permissions'] = $user->permissions()->select('id','name')->orderBy('permission_id', 'ASC')->get();
    //             return response()->json(prepareResult(false, $user, trans('translate.request_successfully_submitted')),config('httpcodes.success'));
    //         } else {
    //             return response()->json(prepareResult(true, [], trans('translate.invalid_username_and_password')),config('httpcodes.unauthorized'));
    //         }
    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }
    // public function logout(Request $request)
    // {
    //     if (Auth::check()) 
    //     {
    //         try
    //         {
    //             $token = Auth::user()->token();
    //             $token->revoke();
    //             auth('api')->user()->tokens->each(function ($token, $key) {
    //                 $token->delete();
    //             });
    //             return response()->json(prepareResult(false, [], trans('translate.logout_message')), config('httpcodes.success'));
    //         }
    //         catch (\Throwable $e) {
    //             \Log::error($e);
    //             return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //         }
    //     }
    //     return response()->json(prepareResult(true, [], trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    // }
    // public function forgotPassword(Request $request)
    // {
    //     $validation = \Validator::make($request->all(),[ 
    //         'email'     => 'required|email'
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
    //     }

    //     try {
    //         $user = User::where('email',$request->email)->withoutGlobalScope('store_id')->first();
    //         if (!$user) {
    //             return response()->json(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
    //         }

    //         if(in_array($user->status, [0,3])) {
    //             return response()->json(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
    //         }

    //         //Delete if entry exists
    //         DB::table('password_resets')->where('email', $request->email)->delete();

    //         $token = Str::random(64);
    //         DB::table('password_resets')->insert([
    //           'email' => $request->email, 
    //           'token' => $token, 
    //           'created_at' => Carbon::now()
    //         ]);

    //         ////////notification and mail//////////
    //         $variable_data = [
    //             '{{name}}' => $user->name,
    //             '{{link}}' => env('FRONT_URL').'/reset-password/'.$token
    //         ];
    //         //notification('forgot-password', $user, $variable_data);
    //         /////////////////////////////////////

    //         return response()->json(prepareResult(false, $request->email, trans('translate.password_reset_link_send_to_your_mail')),config('httpcodes.success'));

    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }

    // public function updatePassword(Request $request)
    // {
    //     $validation = \Validator::make($request->all(),[ 
    //         'password'  => 'required|string|min:6',
    //         'token'     => 'required'
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
    //     }

    //     try {
    //         $tokenExist = DB::table('password_resets')
    //             ->where('token', $request->token)
    //             ->first();
    //         if (!$tokenExist) {
    //             return response()->json(prepareResult(true, [], trans('translate.token_expired_or_not_found')), config('httpcodes.unauthorized'));
    //         }

    //         $user = User::where('email',$tokenExist->email)->withoutGlobalScope('store_id')->first();
    //         if (!$user) {
    //             return response()->json(prepareResult(true, [], trans('translate.user_not_exist')), config('httpcodes.not_found'));
    //         }

    //         if(in_array($user->status, [0,3])) {
    //             return response()->json(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
    //         }

    //         $user = User::where('email', $tokenExist->email)
    //                 ->withoutGlobalScope('store_id')
    //                 ->update(['password' => Hash::make($request->password)]);
 
    //         DB::table('password_resets')->where(['email'=> $tokenExist->email])->delete();

    //         ////////notification and mail//////////
    //         $variable_data = [
    //             '{{name}}' => $user->name
    //         ];
    //        // notification('password-changed', $user, $variable_data);
    //         /////////////////////////////////////


    //         return response()->json(prepareResult(false, $tokenExist->email, trans('translate.password_changed')),config('httpcodes.success'));

    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }

    // public function changePassword(Request $request)
    // {
    //     $validation = \Validator::make($request->all(),[ 
    //         'old_password'  => 'required|string|min:6',
    //         'password'      => 'required|string|min:6'
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json(prepareResult(true, $validation->messages(), trans('translate.validation_failed')), config('httpcodes.bad_request'));
    //     }

    //     try {
            
    //         $user = User::where('email', Auth::user()->email)->withoutGlobalScope('store_id')->first();
            
    //         if(in_array($user->status, [0,3])) {
    //             return response()->json(prepareResult(true, [], trans('translate.account_is_inactive')), config('httpcodes.unauthorized'));
    //         }
    //         if(Hash::check($request->old_password, $user->password)) {
    //             $user = User::where('email', Auth::user()->email)
    //                 ->withoutGlobalScope('store_id')
    //                 ->update(['password' => Hash::make($request->password)]);

    //             ////////notification and mail//////////
    //             $variable_data = [
    //                 '{{name}}' => $user->name
    //             ];
    //             //notification('password-changed', $user, $variable_data);
    //             /////////////////////////////////////
    //         }
    //         else
    //         {
    //             return response()->json(prepareResult(true, [], trans('translate.old_password_not_matched')),config('httpcodes.unauthorized'));
    //         }
            
    //         return response()->json(prepareResult(false, $request->email, trans('translate.password_changed')),config('httpcodes.success'));

    //     } catch (\Throwable $e) {
    //         \Log::error($e);
    //         return response()->json(prepareResult(true, $e->getMessage(), trans('translate.something_went_wrong')), config('httpcodes.internal_server_error'));
    //     }
    // }
    // public function unauthorized(Request $request)
    // {
    //     return response()->json(prepareResult(true, 'Unauthorized. Please login', trans('translate.something_went_wrong')), config('httpcodes.unauthorized'));
    // }
}
