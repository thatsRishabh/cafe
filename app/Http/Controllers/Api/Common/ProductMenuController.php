<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ProductMenu;
use App\Models\Category;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductMenuController extends Controller
{

    public function searchProductMenu(Request $request)
    {
        try {

               $query = Category::select('*')->with('productMenu')
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
            // if(!empty($request->price))
            // {
            //     $query->where('product_menus.price', $request->price);
            // }
            // if(!empty($request->category_id))
            // {
            //     $query->where('product_menus.category_id', $request->category_id);
            // }
            // if(!empty($request->subcategory_id))
            // {
            //     $query->where('product_menus.subcategory_id', $request->subcategory_id);
            // }
            // if(!empty($request->product))
            // {
            //     $query->where('product', 'LIKE', '%'.$request->product.'%');
            // }


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

     public function productMenuList(Request $request)
    {
        try {
            $query = ProductMenu::select('*')
                ->orderBy('id', 'desc');
            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->category_id))
            {
                $query->where('category_id', $request->category_id);
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
            // {($request->parent_id) ? $request->parent_id :null}
            
            // 'name'                    => 'required',
            // // 'description'                => ($request->parent_id) ? ' ': 'required',
            // 'description'                => 'required',
            // 'category_id'                   => 'required|numeric',
            // // 'subcategory_id'                => 'required|numeric',
            // // 'category_id'                   => ($request->parent_id) ? ' ': 'required',
            // // 'subcategory_id'                => ($request->parent_id) ? ' ': 'required',
            // 'price'                      => 'required|numeric',
    //    'price'                      => ($productMenuData->price <= $request->price) ? 'required|declined:false' : 'required',
           
            "product_list.*.name"  => "required", 
            "product_list.*.description"  => "required", 
            "product_list.*.price"  => "required|numeric", 
            "product_list.*.order_duration"  => "required|numeric", 
            "product_list.*.category_id"  => "required|numeric", 
          
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }      
              // if($request->parent_id){
        //     $productMenuData = ProductMenu::where('product_menus.id', $request->parent_id)->get('price')->first();

        //     $validation = Validator::make($request->all(),[     
        //        'price'  => ($productMenuData->price <= $request->price) ? 'required|declined:false' : 'required',   
               
        //     ],
        //     [
        //         'price.declined' => 'Half price is greater than full price'
        //     ]);
        //     if ($validation->fails()) {
        //         return response(prepareResult(false, $validation->errors(), trans('translate.validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
        //     } 
        // }
            $info = new ProductMenu;
            foreach ($request->product_list as $key => $products) {
             
                   $addProduct = new ProductMenu;
                   $addProduct->name =  $products['name'];
                   $addProduct->description =  $products['description'];
                   $addProduct->price =  $products['price'];
                   $addProduct->order_duration =  $products['order_duration'];
                   $addProduct->priority_rank =  $products['priority_rank'];
                   $addProduct->category_id =  $products['category_id'];
                       
                    if(!empty($products['image']))
                        {
                        $file=$products['image'];
                        $filename=time().'.'.$file->getClientOriginalExtension();
                        $addProduct->image =env('CDN_DOC_URL').$products['image']->move('assets\'product_photos',$filename);
                        }

                   $addProduct->save();
               }
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
             // {($request->parent_id) ? $request->parent_id :null}
            
            // 'name'                    => ($request->parent_id) ? ' ': 'required',
            // 'description'                => ($request->parent_id) ? ' ': 'required',
            // 'category_id'                   => 'required|numeric',
            // 'subcategory_id'                => 'required|numeric',
            // 'category_id'                   => ($request->parent_id) ? ' ': 'required',
            // 'subcategory_id'                => ($request->parent_id) ? ' ': 'required',
            // 'price'                      => 'required|numeric',
            // 'price'                      => ($productMenuData->price <= $request->price) ? 'required|declined:false' : 'required',

                // "product_list.*.name"  => "required", 
                // "product_list.*.description"  => "required", 
                // "product_list.*.price"  => "required|numeric", 
                // "product_list.*.order_duration"  => "required|numeric", 
                // "product_list.*.category_id"  => "required|numeric", 

              "name"  => "required", 
                "description"  => "required", 
                "price"  => "required|numeric", 
                "order_duration"  => "required|numeric", 
                "category_id"  => "required|numeric", 
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       

                 $info = ProductMenu::find($id);

     
                if(!empty($request->image))
                {
                    if(gettype($request->image) == "string"){
                        $info->image = $request->image;
                    }
                    else{
                           $file=$request->image;
                            $filename=time().'.'.$file->getClientOriginalExtension();
                            $info->image=env('CDN_DOC_URL').$request->image->move('assets\'product_photos',$filename);
                    }
    
                //   $file=$request->image;
                // $filename=time().'.'.$file->getClientOriginalExtension();
                // $info->image=imageBaseURL().$request->image->move('assets',$filename);
                }
    
                $info->name = $request->name;
                $info->order_duration = $request->order_duration;
                $info->description = $request->description;
                
                $info->priority_rank = $request->priority_rank;
                $info->category_id = $request->category_id;
                // $info->subcategory_id = $request->subcategory_id;
                $info->price = $request->price;
                // $info->parent_id = ($request->parent_id) ? $request->parent_id :null;
                // $info->is_parent = $request->is_parent;
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
            
            $info = ProductMenu::find($id);
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
            
            $info = ProductMenu::find($id);
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


    // public function searchProductMenu(Request $request)
    // {
    //     try {
            
    //         // $query = ProductMenu::join('categories', 'product_menus.subcategory_id', '=', 'categories.id')
    //         //         ->select('product_menus.*', 'categories.name as subCategories_name')
    //         //             // ->select('*')
    //         //         ->whereNull('product_menus.parent_id')
    //         //         // ->with('halfPrice:parent_id,price,name,description,order_duration, category_id')
    //         //          ->with('halfPrice')
    //         //         ->orderBy('product_menus.id', 'desc');

    //         $query = Category::select('*')->with('productMenu')
    //                 ->orderBy('id', 'desc');
                    
    //         if(!empty($request->id))
    //         {
    //             $query->where('id', $request->id);
    //         }
    //         if(!empty($request->name))
    //         {
    //             // $query->where('name', $request->name);
    //             $query->where('name', 'LIKE', '%'.$request->name.'%');
    //         }
    //         // if(!empty($request->price))
    //         // {
    //         //     $query->where('product_menus.price', $request->price);
    //         // }
    //         // if(!empty($request->category_id))
    //         // {
    //         //     $query->where('product_menus.category_id', $request->category_id);
    //         // }
    //         // if(!empty($request->subcategory_id))
    //         // {
    //         //     $query->where('product_menus.subcategory_id', $request->subcategory_id);
    //         // }
    //         // if(!empty($request->product))
    //         // {
    //         //     $query->where('product', 'LIKE', '%'.$request->product.'%');
    //         // }


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

    // public function productMenuList(Request $request)
    // {
    //     try {
    //         $query = ProductMenu::select('*')
    //             ->orderBy('id', 'desc');
    //         if(!empty($request->id))
    //         {
    //             $query->where('id', $request->id);
    //         }
    //         if(!empty($request->category_id))
    //         {
    //             $query->where('category_id', $request->category_id);
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

    // public function store(Request $request)
    // {
    //     // if(!empty($request->parent_id)){
    //     //     $productMenuData = ProductMenu::where('product_menus.id', $request->parent_id)->get('price')->first();
    //     // }
       

    //     $validation = Validator::make($request->all(), [
    //         // {($request->parent_id) ? $request->parent_id :null}
            
    //         // 'name'                    => 'required',
    //         // // 'description'                => ($request->parent_id) ? ' ': 'required',
    //         // 'description'                => 'required',
    //         // 'category_id'                   => 'required|numeric',
    //         // // 'subcategory_id'                => 'required|numeric',
    //         // // 'category_id'                   => ($request->parent_id) ? ' ': 'required',
    //         // // 'subcategory_id'                => ($request->parent_id) ? ' ': 'required',
    //         // 'price'                      => 'required|numeric',
    // //    'price'                      => ($productMenuData->price <= $request->price) ? 'required|declined:false' : 'required',
           
    //         "product_list.*.name"  => "required", 
    //         "product_list.*.description"  => "required", 
    //         "product_list.*.price"  => "required|numeric", 
    //         "product_list.*.order_duration"  => "required|numeric", 
    //         "product_list.*.category_id"  => "required|numeric", 
          
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     } 

    //     // if($request->parent_id){
    //     //     $productMenuData = ProductMenu::where('product_menus.id', $request->parent_id)->get('price')->first();

    //     //     $validation = Validator::make($request->all(),[     
    //     //        'price'  => ($productMenuData->price <= $request->price) ? 'required|declined:false' : 'required',   
               
    //     //     ],
    //     //     [
    //     //         'price.declined' => 'Half price is greater than full price'
    //     //     ]);
    //     //     if ($validation->fails()) {
    //     //         return response(prepareResult(false, $validation->errors(), trans('translate.validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     //     } 
    //     // }

    //     DB::beginTransaction();
    //     try {
    //         // $info = new ProductMenu;

    //         // $category_name = Category::where('categories.id', $request->subcategory_id)->get('name')->first();

    //         // // if(!empty($request->image))
    //         // // {
    //         // //   $file=$request->image;
    //         // // $filename=time().'.'.$file->getClientOriginalExtension();
    //         // // $info->image=$request->image->move('assets',$filename);
    //         // // }

            
    //         // if(!empty($request->image))
    //         // {
    //         //   $file=$request->image;
    //         // $filename=time().'.'.$file->getClientOriginalExtension();
    //         // $info->image=imageBaseURL().$request->image->move('assets',$filename);
    //         // }

    //         // // $file=$request->image;
    //         // // $filename= $file ? time().'.'.$file->getClientOriginalExtension() : "";

    //         // // $info->image=$request->file->move('assets',$filename);
    //         // $info->name = ($request->name) ? $request->name : $category_name->name;
    //         // $info->order_duration = $request->order_duration;
    //         // $info->description = $request->description;
    //         // // $info->image_url = $request->image_url;
    //         // $info->category_id = $request->category_id;
    //         // $info->subcategory_id = $request->subcategory_id;
    //         // $info->price = $request->price;
    //         // $info->parent_id = ($request->parent_id) ? $request->parent_id :null;
    //         // $info->is_parent = $request->is_parent;
    //         // $info->save();


    //         foreach ($request->product_list as $key => $products) {
             
    //                $addProduct = new ProductMenu;
    //                $addProduct->name =  $products['name'];
    //                $addProduct->description =  $products['description'];
    //                $addProduct->price =  $products['price'];
    //                $addProduct->order_duration =  $products['order_duration'];
    //                $addProduct->category_id =  $products['category_id'];
                       
    //                 if(!empty($products['image']))
    //                     {
    //                     $file=$products['image'];
    //                     $filename=time().'.'.$file->getClientOriginalExtension();
    //                     $addProduct->image =imageBaseURL().$products['image']->move('assets',$filename);
    //                     }

    //                $addProduct->save();
    //            }


    //         DB::commit();
    //         // $info['product_menus'] = $info->halfPrice;
    //         return response()->json(prepareResult(true, $addProduct, trans('Your data has been saved successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         DB::rollback();
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been saved')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
    // }

    // public function update(Request $request, $id)
    // {
    //     $validation = Validator::make($request->all(), [
    //          // {($request->parent_id) ? $request->parent_id :null}
            
    //         // 'name'                    => ($request->parent_id) ? ' ': 'required',
    //         // 'description'                => ($request->parent_id) ? ' ': 'required',
    //         // 'category_id'                   => 'required|numeric',
    //         // 'subcategory_id'                => 'required|numeric',
    //         // 'category_id'                   => ($request->parent_id) ? ' ': 'required',
    //         // 'subcategory_id'                => ($request->parent_id) ? ' ': 'required',
    //         // 'price'                      => 'required|numeric',
    //         // 'price'                      => ($productMenuData->price <= $request->price) ? 'required|declined:false' : 'required',

    //             // "product_list.*.name"  => "required", 
    //             // "product_list.*.description"  => "required", 
    //             // "product_list.*.price"  => "required|numeric", 
    //             // "product_list.*.order_duration"  => "required|numeric", 
    //             // "product_list.*.category_id"  => "required|numeric", 

    //           "name"  => "required", 
    //             "description"  => "required", 
    //             "price"  => "required|numeric", 
    //             "order_duration"  => "required|numeric", 
    //             "category_id"  => "required|numeric", 
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }

    //     // if($request->parent_id){
    //     //     $productMenuData = ProductMenu::where('product_menus.id', $request->parent_id)->get('price')->first();

    //     //     $validation = Validator::make($request->all(),[     
    //     //        'price'  => ($productMenuData->price <= $request->price) ? 'required|declined:false' : 'required',   
               
    //     //     ],
    //     //     [
    //     //         'price.declined' => 'Half price is greater than full price'
    //     //     ]);
    //     //     if ($validation->fails()) {
    //     //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     //     } 
    //     // }

    //     DB::beginTransaction();
    //     try {
            
    //         $info = ProductMenu::find($id);

    //         // if(!empty($request->image))
    //         // {
    //         //   $file=$request->image;
    //         // $filename=time().'.'.$file->getClientOriginalExtension();
    //         // $info->image=imageBaseURL().$request->image->move('assets',$filename);
    //         // }
            
    //         if(!empty($request->image))
    //         {
    //             if(gettype($request->image) == "string"){
    //                 $info->image = $request->image;
    //             }
    //             else{
    //                    $file=$request->image;
    //                     $filename=time().'.'.$file->getClientOriginalExtension();
    //                     $info->image=imageBaseURL().$request->image->move('assets',$filename);
    //             }

    //         //   $file=$request->image;
    //         // $filename=time().'.'.$file->getClientOriginalExtension();
    //         // $info->image=imageBaseURL().$request->image->move('assets',$filename);
    //         }

    //         $info->name = $request->name;
    //         $info->order_duration = $request->order_duration;
    //         $info->description = $request->description;
    //         // $info->image_url = $request->image_url;
    //         $info->category_id = $request->category_id;
    //         // $info->subcategory_id = $request->subcategory_id;
    //         $info->price = $request->price;
    //         // $info->parent_id = ($request->parent_id) ? $request->parent_id :null;
    //         // $info->is_parent = $request->is_parent;
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
            
    //         $info = ProductMenu::find($id);
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
            
    //         $info = ProductMenu::find($id);
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
