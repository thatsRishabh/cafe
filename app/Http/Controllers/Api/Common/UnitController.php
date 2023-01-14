<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class UnitController extends Controller
{
    public function searchUnit(Request $request)
    {
        try {

            $query = Unit::select('*')
                        ->orderBy('id', 'desc');
                    if(!empty($request->id))
                    {
                        $query->where('id', $request->id);
                    }
                    if(!empty($request->name))
                    {
                        // $query->where('name', $request->name);
                        $query->where('name', 'LIKE', '%'.$request->name.'%');
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
                        'name'                       => 'required',
                        'abbreiation'                => 'required',
                        // 'minvalue'                   => 'required|numeric',
                        // 'minvalue'                   => ($request->minvalue) > 10 ? '': 'declined:false',
                       
                    ]);
        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
                $info = new Unit;
                $info->name = $request->name;
                $info->abbreiation = $request->abbreiation;
                $info->minvalue = $request->minvalue;
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
            $validation = Validator::make($request->all(), [
                        'name'                       => 'required',
                        'abbreiation'                => 'required',
                        // 'minvalue'                   => 'required|numeric',
                       
                    ]);
           if ($validation->fails()) {
                        return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
                       
           }    

                   $info = Unit::find($id);
                   $info->name = $request->name;
                   $info->abbreiation = $request->abbreiation;
                   $info->minvalue = $request->minvalue;
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
            
            $info = Unit::find($id);
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
            
            $info = Unit::find($id);
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

    // public function searchUnit(Request $request)
    // {
    //     try {
    //         $query = Unit::select('*')
    //             ->orderBy('id', 'desc');
    //         if(!empty($request->id))
    //         {
    //             $query->where('id', $request->id);
    //         }
    //         if(!empty($request->name))
    //         {
    //             // $query->where('name', $request->name);
    //             $query->where('name', 'LIKE', '%'.$request->name.'%');
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

    //         return response(prepareResult(true, $query, trans('Record Featched Successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
    //     } 
    //     catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('Error while featching Records')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
    // }
    // // $table->string('name');
    // // $table->string('abbreiation');
    // // $table->integer('minvalue');
    // public function store(Request $request)
    // {
    //     $validation = Validator::make($request->all(), [
    //         'name'                       => 'required',
    //         'abbreiation'                => 'required',
    //         // 'minvalue'                   => 'required|numeric',
    //         // 'minvalue'                   => ($request->minvalue) > 10 ? '': 'declined:false',
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $info = new Unit;
    //         $info->name = $request->name;
    //         $info->abbreiation = $request->abbreiation;
    //         $info->minvalue = $request->minvalue;
    //         $info->save();
    //         DB::commit();
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
    //         'name'                       => 'required',
    //         'abbreiation'                => 'required',
    //         // 'minvalue'                   => 'required|numeric',
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $info = Unit::find($id);
    //         $info->name = $request->name;
    //         $info->abbreiation = $request->abbreiation;
    //         $info->minvalue = $request->minvalue;
    //         $info->save();
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
            
    //         $info = Unit::find($id);
    //         if($info)
    //         {
    //             // return response(prepareResult(false, $info, trans('Record Featched Successfully')), config('httpcodes.success'));
    //             return response(prepareResult(true, $info, trans('Record Fatched Successfully')), 200 , ['Result'=>'httpcodes.found']);
    //         }
    //         return response(prepareResult(false, [], trans('Error while fatching Records')),500,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    // }

    // public function destroy($id)
    // {
    //     try {
            
    //         $info = Unit::find($id);
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
