<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductInfo;
use App\Models\Unit;


class ProductInfoController extends Controller
{
    public function searchProductInfo(Request $request)
    {
        try {
            // $query = DB::table('product_infos')
            //             ->join('units', 'product_infos.unit_id', '=', 'units.id')
            //             ->select('product_infos.*', 'units.name as units_name', 'units.minvalue as units_minvalue')
            //             ->orderBy('product_infos.id', 'desc');
            //           // in above we have to specify that it has to sort according to ID of which table, product_infos or Unit

            $query = ProductInfo::select('*')
                            ->with('unitName:id,name,minvalue')
                            ->orderBy('id', 'desc');
            
                        if(!empty($request->id))
                        {
                            $query->where('product_infos.id',  $request->id);
                            // in above we are specifying that it has to match from Product_infos id
                        }
                        if(!empty($request->unit_id))
                        {
                            $query->where('product_infos.unit_id',  $request->unit_id);
                        }
                        if(!empty($request->current_quanitity))
                        {
                            $query->where('product_infos.current_quanitity',  $request->current_quanitity);
                        }
                        if(!empty($request->name))
                        {
                            $query->where('product_infos.name','LIKE', '%'.$request->name.'%');
                        }
                        if(!empty($request->description))
                        {
                            $query->where('description', 'LIKE', '%'.$request->description.'%');
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
            $nameCheck = ProductInfo::where('name', $request->name)->first();
            $validation = Validator::make($request->all(), [
                            'name'                      => $nameCheck ? 'required|declined:false' : 'required',
                            // 'name'                       => 'required|unique:App\Models\ProductInfo,name',
                            'description'                => 'required',
                            'unit_id'                    => 'required|numeric',
                            // 'minimum_qty'              => 'required|numeric',
                            'current_quanitity'                  => 'required|gte:0.1',
                            'price'                      => 'required|numeric',
                            
                           
                        ],
                        [
                            'name.declined' =>          'Name already exists',
                        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
                    $info = new ProductInfo;
                    $info->name = $request->name;
                    $info->description = $request->description;
                    $info->unit_id = $request->unit_id;
                    $info->current_quanitity = unitConversion($request->unit_id, $request->current_quanitity);
                    // $info->minimum_qty = $request->minimum_qty;
                    $info->price = $request->price;
                    
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

          $nameCheck = ProductInfo::where('id',  $id)->get('name')->first();

           $validation = Validator::make($request->all(), [
               'name'                       => 'required',
               'name'                      => $nameCheck ->name == $request->name ? 'required' : 'required|unique:App\Models\ProductInfo,name',
               'description'                => 'required',
               'unit_id'                    => 'required|numeric',
               // 'minimum_qty'                => 'required|numeric',
            'current_quanitity'           => 'required|gte:0.1',
               'price'                      => 'required|numeric',
                       
           ]);
           if ($validation->fails()) {
                        return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
                       
           }    

           $info = ProductInfo::find($id);
              $info->name = $request->name;
           $info->description = $request->description;
           $info->unit_id = $request->unit_id;
           $info->current_quanitity = unitConversion($request->unit_id, $request->current_quanitity);
           // $info->minimum_qty = $request->minimum_qty;
           $info->price = $request->price;
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
            
            $info = ProductInfo::find($id);
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
            
            $info = ProductInfo::find($id);
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

//     public function searchProductInfo(Request $request)
//     {
//     try {

//             $query = DB::table('product_infos')
//             ->join('units', 'product_infos.unit_id', '=', 'units.id')
//             ->select('product_infos.*', 'units.name as units_name', 'units.minvalue as units_minvalue')
//             ->orderBy('product_infos.id', 'desc');
//           // in above we have to specify that it has to sort according to ID of which table, product_infos or Unit

//             if(!empty($request->id))
//             {
//                 $query->where('product_infos.id',  $request->id);
//                 // in above we are specifying that it has to match from Product_infos id
//             }
//             if(!empty($request->name))
//             {
//                 $query->where('product_infos.name','LIKE', '%'.$request->name.'%');
//             }
//             if(!empty($request->description))
//             {
//                 $query->where('description', 'LIKE', '%'.$request->description.'%');
//             }


//             if(!empty($request->per_page_record))
//             {
//                 $perPage = $request->per_page_record;
//                 $page = $request->input('page', 1);
//                 $total = $query->count();
//                 $result = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

//                 $pagination =  [
//                     'data' => $result,
//                     'total' => $total,
//                     'current_page' => $page,
//                     'per_page' => $perPage,
//                     'last_page' => ceil($total / $perPage)
//                 ];
//                 $query = $pagination;
//             }
//             else
//             {
//                 $query = $query->get();
//             }

//             return response(prepareResult(true, $query, trans('Record Featched Successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
//         } 
//         catch (\Throwable $e) {
//             Log::error($e);
//             return response()->json(prepareResult(false, $e->getMessage(), trans('Error while featching Records')), 500,  ['Result'=>'Your data has not been saved']);
//         }
//     }
    
//     public function store(Request $request)
//     {
//         $validation = Validator::make($request->all(), [
//             'name'                       => 'required|unique:App\Models\ProductInfo,name',
//             'description'                => 'required',
//             'unit_id'                    => 'required|numeric',
//             // 'minimum_qty'              => 'required|numeric',
//             'current_quanitity'                  => 'required|gte:1',
//             'price'                      => 'required|numeric',
            
           
//         ]);

//         if ($validation->fails()) {
//             return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
//         }
    
//         DB::beginTransaction();
//         try {
//             $info = new ProductInfo;
//             $info->name = $request->name;
//             $info->description = $request->description;
//             $info->unit_id = $request->unit_id;
//             $info->current_quanitity = unitConversion($request->unit_id, $request->current_quanitity);
//             // $info->minimum_qty = $request->minimum_qty;
//             $info->price = $request->price;
            
//             $info->save();
//             DB::commit();
//             return response()->json(prepareResult(true, $info, trans('Your data has been saved successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
//         } catch (\Throwable $e) {
//             Log::error($e);
//             DB::rollback();
//             return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been saved')), 500,  ['Result'=>'Your data has not been saved']);
//         }
//     }

//     public function update(Request $request, $id)
//     {
//         $nameCheck = ProductInfo::where('id',  $id)->get('name')->first();

//         $validation = Validator::make($request->all(), [
//             'name'                       => 'required',
//             'name'                      => $nameCheck ->name == $request->name ? 'required' : 'required|unique:App\Models\ProductInfo,name',
//             'description'                => 'required',
//             'unit_id'                    => 'required|numeric',
//             // 'minimum_qty'                => 'required|numeric',
//             'current_quanitity'           => 'required|gte:1',
//             'price'                      => 'required|numeric',
           
//         ]);

//         if ($validation->fails()) {
//             return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
//         }
    
//         DB::beginTransaction();
//         try {
//             $info = ProductInfo::find($id);
//             $info->name = $request->name;
//             $info->description = $request->description;
//             $info->unit_id = $request->unit_id;
//             $info->current_quanitity = unitConversion($request->unit_id, $request->current_quanitity);
//             // $info->minimum_qty = $request->minimum_qty;
//             $info->price = $request->price;
//             $info->save();
//             DB::commit();
//             return response()->json(prepareResult(true, $info, trans('Your data has been Updated successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
//         } catch (\Throwable $e) {
//             Log::error($e);
//             DB::rollback();
//             return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been Updated')), 500,  ['Result'=>'Your data has not been saved']);
//         }
//     }

//     public function show($id)
//     {
//         try {
            
//             $info = ProductInfo::find($id);
//             if($info)
//             {
//                 // return response(prepareResult(false, $info, trans('translate.fetched_records')), config('httpcodes.success'));
//                 return response(prepareResult(true, $info, trans('Record Featched Successfully')), 200 , ['Result'=>'httpcodes.found']);
//             }
//             return response(prepareResult(false, [], trans('Error while featching Records')),500,  ['Result'=>'httpcodes.not_found']);
//         } catch (\Throwable $e) {
//             Log::error($e);
//             return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
//         }
//     }

//     public function destroy($id)
//     {
//         try {
            
//             $info = ProductInfo::find($id);
//             if($info)
//             {
//                 $result=$info->delete();
//                 return response(prepareResult(true, $result, trans('Record Id Deleted Successfully')), 200 , ['Result'=>'httpcodes.found']);
//             }
//             return response(prepareResult(false, [], trans('Record Id Not Found')),500,  ['Result'=>'httpcodes.not_found']);
//         } catch (\Throwable $e) {
//             Log::error($e);
//             return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
//         }
//     }

//     // public function joinData()
//     // {
//     //     $info = DB::table('product_infos')
//     //     ->join('units', 'product_infos.unit_id', '=', 'units.id')
//     //     // ->join('orders', 'users.id', '=', 'orders.user_id')
//     //     // ->select('units.*','units.id as units_id', 'units.name as units_name ') 
//     //     ->select('product_infos.*','units.id as units_id', 'units.name as units_name ') 
//     //     ->orderBy('id', 'desc')
//     //     ->get();

//     //     return  $info;
//     //     // print_r($info->all());
//     // }
    
}
