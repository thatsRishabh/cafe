<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ProductMenu;
use App\Models\Category;
use App\Models\ProductInfo;
use App\Models\Unit;
use App\Models\Recipe;
use App\Models\RecipeContains;
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
            // $query = ProductMenu::select('*')
                if(!empty($request->priority_rank)){
                    $query = ProductMenu::select('*') ->orderBy('priority_rank', 'asc');
                }else{ 
                    $query = ProductMenu::select('*')->orderBy('id', 'desc');
                }
               

            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->name))
            {
                // $query->where('name', $request->name);
                $query->where('name', 'LIKE', '%'.$request->name.'%');
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
                   $addProduct->product_info_stock_id =  $products['product_info_stock_id'];
                   $addProduct->without_recipe =  $products['without_recipe'];
                   $addProduct->quantity = $products['quantity'];
                //    $addProduct->unit_id = $products['unit_id'];
                   $addProduct->name =  $products['name'];
                   $addProduct->description =  $products['description'];
                   $addProduct->price =  $products['price'];
                   $addProduct->order_duration =  $products['order_duration'];
                   $addProduct->priority_rank =  $products['priority_rank'] ? $products['priority_rank'] : "10000" ;
                   $addProduct->category_id =  $products['category_id'];

                     if(!empty($products['image']))
                        {
                        $file=$products['image'];
                        $filename=time().'.'.$file->getClientOriginalExtension();
                        if ($file->move('assets/product_photos', $filename)) {
                            $addProduct->image=env('CDN_DOC_URL').'assets/product_photos/'.$filename.'';
                        }}
                   $addProduct->save();
                    }

                //     foreach ($request->product_list as $key => $products) {
                //      if($products['without_recipe']==1){
                //   //    creating recipe
                //     $addRecipe = new Recipe;
                //     $addRecipe->name = $products['name'];
                //     $addRecipe->product_menu_id = $addProduct->id;
                //     $addRecipe->description =$products['description'];
                //     // $addRecipe->recipe_status = $request->recipe_status;
                //     $addRecipe->save();
           
           
                //     $product_info_name = ProductInfo::where('product_infos.id', $products['product_info_stock_id'])->get('name')->first();
                //     $unitInfo = Unit::find( $products['unit_id']);

                //     $addRecipeContains = new RecipeContains;
                //     $addRecipeContains->recipe_id =  $$addRecipe->id;
                //     //    $addRecipe->name = $recipe['name'];
                //     $addRecipeContains->name = $product_info_name->name; 
                //     $addRecipeContains->product_info_stock_id = $products['product_info_stock_id'];
                //     $addRecipeContains->quantity = $products['quantity'];
                //     $addRecipeContains->unit_id = $products['unit_id'];
                //     $addRecipeContains->unit_name = $unitInfo->name;
                //     $addRecipeContains->unit_minValue = $unitInfo->minvalue;
                //     $addRecipeContains->save();
                //    }
                // }
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
                        //    $file=$request->image;
                        //     $filename=time().'.'.$file->getClientOriginalExtension();
                        //     $info->image=env('CDN_DOC_URL').$request->image->move('assets\'product_photos',$filename);
                        if ($request->hasFile('image')) {
                            $file = $request->file('image');
                            $filename=time().'.'.$file->getClientOriginalExtension();
                            if ($file->move('assets/product_photos', $filename)) {
                                $info->image=env('CDN_DOC_URL').'assets/product_photos/'.$filename.'';
                            }
                           }
                    }
    
                //   $file=$request->image;
                // $filename=time().'.'.$file->getClientOriginalExtension();
                // $info->image=imageBaseURL().$request->image->move('assets',$filename);
                }

                $info->product_info_stock_id = $request->product_info_stock_id;
                $info->without_recipe = $request->without_recipe;
                $info->quantity = $request->quantity;
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

}
