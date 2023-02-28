<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderContain;
use App\Models\ProductMenu;
use App\Models\Packaging;
use App\Models\PackagingContents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
// use Barryvdh\DomPDF\Facade\PDF;
use PDF;
use App\Models\Recipe;
use App\Models\RecipeContains;


class OrderController extends Controller
{

    public function searchOrder(Request $request)
    {
        try {

         $query = Order::select('*')
                    ->with('orderContains','cafeDetails','cafeDetail:cafe_id,email,mobile')
                    ->orderBy('id', 'desc');

            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->table_number))
            {
                $query->where('table_number', $request->table_number);
            }
            if(!empty($request->order_status))
            {
                $query->where('order_status', $request->order_status);
            }
            
            // if(!empty($request->created_at))
            // {
            //     $query->where('created_at', $request->created_at);
            // }
           
            // date wise filter with order status
            // print_r($request->all());
            // die;
            if(!empty($request->end_date) && !empty($request->order_status))
            {
                $query->where('order_status', $request->order_status)->whereDate('updated_at', '=', $request->end_date);
            }

           // date wise filter from here
           if(!empty($request->from_date) && !empty($request->end_date))
           {
               $query->whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->end_date);
           }
           elseif(!empty($request->from_date) && empty($request->end_date))
           {
               $query->whereDate('created_at', '>=', $request->from_date);
           }
           elseif(empty($request->from_date) && !empty($request->end_date))
           {
               $query->whereDate('created_at', '<=', $request->end_date);
           }
          // print_r($query->toSql());
          // die;

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
                 'order_status'                   => 'nullable|numeric',
          
           
        ]);
        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        } 
        
            if($request->order_status == "2"){

            foreach ($request->order_contains as $key => $recipe1) {
                
                $recipeID = Recipe::where('product_menu_id', $recipe1['product_menu_id'])->get('id')->first();

                // return  recipeDeductionValidation($recipeID->id, $recipe1['quantity']);
                $validation = Validator::make($request->all(),[     

                   
                    "order_contains.*.product_menu_id"  =>$recipeID ? recipeDeductionValidation($recipeID->id, $recipe1['quantity']) : 'required', 
                    
                 ],
                 [
                     'order_contains.*.product_menu_id.declined' => 'Less value left in stock',
                 ]
             );

             
             if ($validation->fails()) {
                return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
            } 
            }

        }

        if($request->order_status == "2"){

            foreach ($request->order_contains as $key => $order1) {
                
           
                $productMenuItem1 = ProductMenu::find( $order1['product_menu_id']);
               
                $validation = Validator::make($request->all(),[     

                    "order_contains.*.product_menu_id"  => $productMenuItem1->without_recipe==1 ? withoutRecipeDeductionValidation($productMenuItem1->product_info_stock_id, $order1['quantity']) : 'required',
                    
                 ],
                 [
                     'order_contains.*.product_menu_id.declined' => 'Less value left in stock',
                 ]
             );

             
             if ($validation->fails()) {
                return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
            } 
            }

        }

                 $info = new Order;
                $info->table_number = $request->table_number;
                $info->customer_id = $request->customer_id;
                $info->mode_of_transaction = $request->mode_of_transaction;
                $info->cartTotalQuantity = $request->cartTotalQuantity;
                $info->cartTotalAmount = $request->cartTotalAmount;
                $info->taxes = $request->taxes;
                $info->netAmount = $request->netAmount;
                $info->order_status = $request->order_status;
                $info->order_parcel = $request->order_parcel;
                $info->duration_expired = $request->duration_expired;
                $info->save();
    
                foreach ($request->order_contains as $key => $order) {
    
                    // search query for data from another table
                    $productMenuItem = ProductMenu::find( $order['product_menu_id']);
                    
                    $addorder = new OrderContain;
                    $addorder->order_id =  $info->id;
                    $addorder->product_menu_id = $order['product_menu_id'];
                    $addorder->category_id = $order['category_id'];
                    // $addorder->unit_id = $order['unit_id'];
                    $addorder->order_duration = $order['order_duration'];
                    $addorder->instructions = $order['instructions'] ?? "";
    
                    // below data is from another table
                    $addorder->name = $productMenuItem->name;
                    // $addorder->name = $order['name'];
                    $addorder->quantity = $order['quantity'];
    
                     // below data is from another table
                    // $addorder->price = $productMenuItem->price;
                    $addorder->price = $order['price'];
                    // $addorder->netPrice = $order['quantity'] * $productMenuItem->price ;
                    $addorder->netPrice = $order['netPrice'];
                    $addorder->save();
                    
                    // this will delete quantity from stock as per reicpe
                    // $recipeID = Recipe::where('product_menu_id', $order['product_menu_id'])->get('id')->first();
                    // recipeDeduction($recipeID->id);
    
                     // this will delete quantity from stock as per reicpe only when order is approved
                     if($request->order_status == "2"){
                     $recipeID = Recipe::where('product_menu_id', $order['product_menu_id'])->get('id')->first();
                     $recipeID ? recipeDeduction($recipeID->id, $order['quantity']) : '';

                     $productMenuItem->without_recipe==1 ? withoutRecipeDeduction($productMenuItem->product_info_stock_id, $order['quantity']) : '';

                     $packagingID = Packaging::where('category_id', $order['category_id'])->get('id')->first();
                     if($packagingID){
                     $request->order_parcel==1 ? packagingDeduction($packagingID->id, $order['quantity']) : '';
                     }
                     }
                }
     
    
                 DB::commit();
                 $info['order_contains'] = $info->orderContains;
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
                        // 'table_number'                    => 'required|numeric',
                        'order_status'                   => 'nullable|numeric',
                        'taxes'                      => 'nullable|numeric',
                       
                    ]);
           if ($validation->fails()) {
                        return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
                       
           } 
        if($request->order_status == "2"){

            foreach ($request->order_contains as $key => $recipe1) {
                
                $recipeID = Recipe::where('product_menu_id', $recipe1['product_menu_id'])->get('id')->first();

                // return  recipeDeductionValidation($recipeID->id, $recipe1['quantity']);
                $validation = Validator::make($request->all(),[     


                    "order_contains.*.product_menu_id"  =>$recipeID ? recipeDeductionValidation($recipeID->id, $recipe1['quantity']) : 'required', 
                    
                 ],
                 [
                     'order_contains.*.product_menu_id.declined' => 'Less value left in stock',
                 ]
             );

             
             if ($validation->fails()) {
                return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
            } 
            }

        }   
                $info = Order::find($id);
                $info->table_number = $request->table_number;
                $info->customer_id = $request->customer_id;
                $info->mode_of_transaction = $request->mode_of_transaction;
                $info->cartTotalQuantity = $request->cartTotalQuantity;
                $info->cartTotalAmount = $request->cartTotalAmount;
                $info->taxes = $request->taxes;
                $info->netAmount = $request->netAmount;
                $info->order_status = $request->order_status;
                $info->order_parcel = $request->order_parcel;
                $info->duration_expired = $request->duration_expired;
                $info->save();
    
               $deletOld = OrderContain::where('order_id', $id)->delete();
               
                foreach ($request->order_contains as $key => $order) {
                   // search query for data from another table
                   $productMenuItem = ProductMenu::find( $order['product_menu_id']);
                    
                   $addorder = new OrderContain;
                   $addorder->order_id =  $info->id;
                   $addorder->product_menu_id = $order['product_menu_id'];
                   $addorder->category_id = $order['category_id'];
                //    $addorder->unit_id = $order['unit_id'];
                   $addorder->order_duration = $order['order_duration'];
                   $addorder->instructions = $order['instructions'] ?? "";
                   
                   // below data is from another table
                   $addorder->name = $productMenuItem->name;
                //    $addorder->name = $order['name'];
                   $addorder->quantity = $order['quantity'];
    
                    // below data is from another table
                   // $addorder->price = $productMenuItem->price;
                   $addorder->price = $order['price'];
                   // $addorder->netPrice = $order['quantity'] * $productMenuItem->price ;
                   $addorder->netPrice = $order['netPrice'];
                   $addorder->save();
    
                     // this will delete quantity from stock as per reicpe only when order is approved
                    // $recipeID = Recipe::where('product_menu_id', $order['product_menu_id'])->get('id')->first();
                    // $recipeID ? recipeDeduction($recipeID->id, $order['quantity']) : '';
                
                    if($request->order_status == "2"){
                        $recipeID = Recipe::where('product_menu_id', $order['product_menu_id'])->get('id')->first();
                        $recipeID ? recipeDeduction($recipeID->id, $order['quantity']) : '';

                        $productMenuItem->without_recipe==1 ? withoutRecipeDeduction($productMenuItem->product_info_stock_id, $order['quantity']) : '';

                        }
                }
     
                //     // database sum querry
                // $quantitySum= DB::table('order_contains')->where('order_contains.order_id', $info->id)->sum('quantity');
                // $amountSum= DB::table('order_contains')->where('order_contains.order_id', $info->id)->sum('netPrice');
    
                // $info = Order::find( $info->id);
                // $info->cartTotalQuantity = $quantitySum;
                // $info->cartTotalAmount = $amountSum;
                // $info->taxes = $request->taxes;
                // $info->netAmount = $amountSum + $request->taxes;
                // $info->save();
                DB::commit();
                $info['order_contains'] = $info->orderContains;
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
            
            $info = Order::find($id);
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
            
            $info = Order::find($id);
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
     public function printOrder($id) {
       

                try {
            
                    $info = Order::find($id);
                    if($info)
                    {
                                $filename = $id."-".time().".pdf";
                                $data =[
                                    'order_id'=>$id,
                                ];
                                $customPaper = array(0,0,280,960);
                                $pdf = PDF::loadView('order-pdf', $data)->setPaper( $customPaper);
                                $pdf->save('pdf_bill'.$filename);
                                $url = env('CDN_DOC_URL').'pdf_bill'.$filename;
                        
                                $info = Order::find($id);
                                $info->bill_pdf = $url;
                                $info->save();

                                return prepareResult(true,'print out successful' ,$url, 200); 
                       
                    }
                    return prepareResult(false,'Record Id Not Found' ,[], 500);
                } catch (\Throwable $e) {
                    Log::error($e);
                    return prepareResult(false,'something_went_wrong' ,$e->getMessage(), 500);
                    
                }
            }

    //     public function printOrder($id) {
       

    //             try {
            
    //                 $info = Order::find($id);
    //                 if($info)
    //                 {
    //                             $filename = $id."-".time().".pdf";
    //                             $data =[
    //                                 'order_id'=>$id,
    //                             ];
    //                             $customPaper = array(0,0,280,960);
    //                             $pdf = PDF::loadView('order-pdf', $data)->setPaper( $customPaper);
    //                             $pdf->save('pdf_bill'.$filename);
    //                             $url = imageBaseURL().'pdf_bill'.$filename;
                        
    //                             $info = Order::find($id);
    //                             $info->bill_pdf = $url;
    //                             $info->save();


    //                     return response(prepareResult(true, $url, trans('print out successful')), 200 , ['Result'=>'httpcodes.found']);
    //                 }
    //                 return response(prepareResult(false, [], trans('Record Id Not Found')),500,  ['Result'=>'httpcodes.not_found']);
    //             } catch (\Throwable $e) {
    //                 Log::error($e);
    //                 return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //             }
    //         }


}
