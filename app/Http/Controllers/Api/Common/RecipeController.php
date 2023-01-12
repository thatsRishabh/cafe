<?php

namespace App\Http\Controllers\Api\Common;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\RecipeContains;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductInfo;
use App\Models\Unit;

class RecipeController extends Controller
{

    public function searchRecipe(Request $request)
    {
        try {
            $query = Recipe::select('*')
            ->join('product_menus', 'recipes.product_menu_id', '=', 'product_menus.id')
            ->select('recipes.*','product_menus.name as product_menu_name' )
                    ->with('recipeMethods:recipe_id,name,quantity,unit_id,product_info_stock_id,unit_name,unit_minValue')
                    ->orderBy('id', 'desc');

        //   $query = RecipeContains::select('*')
        //         ->with('recipes')
        //         ->orderBy('id', 'desc');
        //        in above we can retrive parrent data via child

            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->name))
            {
                // $query->where('name', $request->name);
                $query->where('name', 'LIKE', '%'.$request->name.'%');
            }
            if(!empty($request->product_menu_name))
            {
                // $query->where('product_menus.name', $request->product_menu_name);
                $query->where('product_menus.name', 'LIKE', '%'.$request->product_menu_name.'%');
            }
            if(!empty($request->recipe_status))
            {
                $query->where('recipe_status', $request->recipe_status);
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

            return response(prepareResult(true, $query, trans('Record Featched Successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
        } 
        catch (\Throwable $e) {
            Log::error($e);
            return response()->json(prepareResult(false, $e->getMessage(), trans('Error while featching Records')), 500,  ['Result'=>'Your data has not been saved']);
        }
    }

    public function store(Request $request)
    {
       

        $validation = Validator::make($request->all(), [
            // 'name'                    => 'required',
            'recipe_status'                    => 'required|numeric',
            // 'description'                => 'required',
            'product_menu_id'                => 'required|unique:App\Models\Recipe,product_menu_id',
            // 'name'                      => 'required',
            // 'quantity'                   => 'nullable|numeric',
            // 'unit_id'                => 'nullable|numeric',
           
            // "recipe_methods.*.unit_id"  => "required|numeric", 
           
            "recipe_methods.*.quantity" => "required|numeric", 

        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
        }

        if($request->recipe_methods){

            foreach ($request->recipe_methods as $key => $recipe1) {
                $oldValue1 = ProductInfo::where('product_infos.id', $recipe1['product_info_stock_id'])->get('current_quanitity')->first();
              
                $validation = Validator::make($request->all(),[      
                    "recipe_methods.*.quantity"  => ($oldValue1->current_quanitity < unitConversion($recipe1['unit_id'], $recipe1['quantity']) ) ? 'required|declined:false' : 'required|gte:1', 
                
                    "recipe_methods.*.unit_id"  => unitSimilarTypeCheck($recipe1['unit_id'], $recipe1['product_info_stock_id']), 
                   
                    
                 ],
                 [
                     'recipe_methods.*.quantity.declined' => 'Less value left in stock',
                     'recipe_methods.*.unit_id.declined' => 'Invalid Unit Type',
                 ]
             );

             
            if ($validation->fails()) {
                return response(prepareResult(false, $validation->errors(), trans('translate.validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
            } 
            }

        }

        DB::beginTransaction();
        try {
            $info = new Recipe;
            $info->name = $request->name;
            $info->product_menu_id = $request->product_menu_id;
            $info->description = $request->description;
            $info->recipe_status = $request->recipe_status;
            $info->save();
           
           foreach ($request->recipe_methods as $key => $recipe) {
            $product_info_name = ProductInfo::where('product_infos.id', $recipe['product_info_stock_id'])->get('name')->first();

            $unitInfo = Unit::find( $recipe['unit_id']);

               $addRecipe = new RecipeContains;
               $addRecipe->recipe_id =  $info->id;
            //    $addRecipe->name = $recipe['name'];
              $addRecipe->name = $product_info_name->name; 
               $addRecipe->product_info_stock_id = $recipe['product_info_stock_id'];
               $addRecipe->quantity = $recipe['quantity'];
               $addRecipe->unit_id = $recipe['unit_id'];
               $addRecipe->unit_name = $unitInfo->name;
               $addRecipe->unit_minValue = $unitInfo->minvalue;
               $addRecipe->save();
               
             // getting old stock value
            // $oldValue = ProductInfo::where('product_infos.id', $recipe['product_info_stock_id'])->get('current_quanitity')->first();
            //  // updating the productinfo table as well
            //  $updateStock = ProductInfo::find($recipe['product_info_stock_id']);
            //  $updateStock->current_quanitity =  $oldValue->current_quanitity - unitConversion($recipe['unit_id'], $recipe['quantity']);
            //  $updateStock->save();

           }

            DB::commit();
            $info['recipe_contains'] = $info->recipeMethods;
            return response()->json(prepareResult(true, $info, trans('Your data has been saved successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollback();
            return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been saved')), 500,  ['Result'=>'Your data has not been saved']);
        }
    }

    public function update(Request $request, $id)
    {
        $email_product_menu_id = Recipe::where('id',  $id)->get('product_menu_id')->first();


        $validation = Validator::make($request->all(), [
            // 'name'                    => 'required',
            // 'description'                => 'required',
            'product_menu_id'            => $email_product_menu_id->product_menu_id == $request->product_menu_id ? 'required' : 'required|unique:App\Models\Recipe,product_menu_id',
             'recipe_status'                    => 'required|numeric',
            // 'name'                      => 'required',
            // 'quantity'                   => 'nullable|numeric',
            // 'unit_id'                => 'nullable|numeric',
            "recipe_methods.*.unit_id"  => "required|numeric", 
            "recipe_methods.*.quantity" => "required|numeric", 
           
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
        }

        if($request->recipe_methods){

            foreach ($request->recipe_methods as $key => $recipe1) {
                $oldValue1 = ProductInfo::where('product_infos.id', $recipe1['product_info_stock_id'])->get('current_quanitity')->first();
              
                $validation = Validator::make($request->all(),[      
                    "recipe_methods.*.quantity"  => ($oldValue1->current_quanitity < unitConversion($recipe1['unit_id'], $recipe1['quantity']) ) ? 'required|declined:false' : 'required|gte:1', 
                    "recipe_methods.*.unit_id"  => unitSimilarTypeCheck($recipe1['unit_id'], $recipe1['product_info_stock_id']), 
                    
                 ],
                 [
                     'recipe_methods.*.quantity.declined' => 'Less value left in stock',
                     'recipe_methods.*.unit_id.declined' => 'Invalid Unit Type',
                 ]
             );

            }
          
            if ($validation->fails()) {
                return response(prepareResult(false, $validation->errors(), trans('translate.validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
            } 
        }

        
        DB::beginTransaction();
        try {

            $info = Recipe::find($id);
            $info->name = $request->name;
            $info->product_menu_id = $request->product_menu_id;
            $info->description = $request->description;
            $info->recipe_status = $request->recipe_status;
            $info->save();
           
            
        $deletOld = RecipeContains::where('recipe_id', $id)->delete();
           foreach ($request->recipe_methods as $key => $recipe) {
            // $product_info_name = ProductInfo::where('product_infos.id', $recipe['product_info_stock_id'])->get('name')->first();

            // // $addRecipe=RecipeContains::find($recipe['id']);
            //    $addRecipe= new RecipeContains;
            //     $addRecipe->recipe_id = $id;
            //    $addRecipe->name = $product_info_name->name;
            //    $addRecipe->product_info_stock_id = $recipe['product_info_stock_id'];
            //    $addRecipe->quantity = $recipe['quantity'];
            //    $addRecipe->unit_id = $recipe['unit_id'];
            //    $addRecipe->save();
               
            //     // getting old stock value
            // $oldValue = ProductInfo::where('product_infos.id', $recipe['product_info_stock_id'])->get('current_quanitity')->first();
            // // updating the productinfo table as well
            // $updateStock = ProductInfo::find($recipe['product_info_stock_id']);
            // $updateStock->current_quanitity =  $oldValue->current_quanitity - unitConversion($recipe['unit_id'], $recipe['quantity']);
            // $updateStock->save();

            $product_info_name = ProductInfo::where('product_infos.id', $recipe['product_info_stock_id'])->get('name')->first();

            $unitInfo = Unit::find( $recipe['unit_id']);

               $addRecipe = new RecipeContains;
               $addRecipe->recipe_id =  $info->id;
            //    $addRecipe->name = $recipe['name'];
              $addRecipe->name = $product_info_name->name; 
               $addRecipe->product_info_stock_id = $recipe['product_info_stock_id'];
               $addRecipe->quantity = $recipe['quantity'];
               $addRecipe->unit_id = $recipe['unit_id'];
               $addRecipe->unit_name = $unitInfo->name;
               $addRecipe->unit_minValue = $unitInfo->minvalue;
               $addRecipe->save();
               
             // getting old stock value
            // $oldValue = ProductInfo::where('product_infos.id', $recipe['product_info_stock_id'])->get('current_quanitity')->first();
            //  // updating the productinfo table as well
            //  $updateStock = ProductInfo::find($recipe['product_info_stock_id']);
            //  $updateStock->current_quanitity =  $oldValue->current_quanitity - unitConversion($recipe['unit_id'], $recipe['quantity']);
            //  $updateStock->save();
           
           }

            DB::commit();
            $info['recipe_contains'] = $info->recipeMethods;
            return response()->json(prepareResult(true, $info, trans('Your data has been Updated successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollback();
            return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been Updated')), 500,  ['Result'=>'Your data has not been saved']);
        }
    }

    public function show($id)
    {
        try {
            
            $info = Recipe::with('recipeMethods')->find($id);
            if($info)
            {
                // return response(prepareResult(false, $info, trans('Record Featched Successfully')), config('httpcodes.success'));
                return response(prepareResult(true, $info, trans('Record Featched Successfully')), 200 , ['Result'=>'httpcodes.found']);
            }
            return response(prepareResult(false, [], trans('Error while featching Records')),500,  ['Result'=>'httpcodes.not_found']);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
        }
    }

    public function destroy($id)
    {
        try {
            
            $info = Recipe::find($id);
            if($info)
            {
                $result=$info->delete();
                return response(prepareResult(true, $result, trans('Record Id Deleted Successfully')), 200 , ['Result'=>'httpcodes.found']);
            }
            return response(prepareResult(false, [], trans('Record Id Not Found')),500,  ['Result'=>'httpcodes.not_found']);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
        }
    }

}
