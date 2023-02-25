<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Packaging;
use App\Models\PackagingContents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PackagingController extends Controller
{
    //
    public function searchPackaging(Request $request)
    {
        try {

            $query = Packaging::select('*')
                            ->with('packagingMaterial:packaging_id,quantity,product_info_stock_id','categoryName:id,name','packagingMaterial.productName:id,name')
                            ->orderBy('id', 'desc');
                    if(!empty($request->id))
                    {
                        $query->where('id', $request->id);
                    }
                    if(!empty($request->name))
                    {
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
                        'category_id'                    => 'required|numeric',
                        // 'product_menu_id'                => 'required|unique:App\Models\Recipe,product_menu_id',
                        "packaging_content.*.product_info_stock_id" => "required|numeric", 
                        "packaging_content.*.quantity" => "required|numeric", 
            
                    ]);
            
        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }  
        $info = new Packaging;
        $info->category_id = $request->category_id;
        $info->save();
       
       foreach ($request->packaging_content as $key => $content) {

           $addcontent = new PackagingContents;
           $addcontent->packaging_id =  $info->id;
        //    $addRecipe->name = $recipe['name'];
           $addcontent->product_info_stock_id = $content['product_info_stock_id'];
           $addcontent->quantity = $content['quantity'];
           $addcontent->save();

       }

            $info['packaging_contents'] = $info->packagingMaterial;
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
                'category_id'                    => 'required|numeric',
                // 'product_menu_id'                => 'required|unique:App\Models\Recipe,product_menu_id',
                "packaging_content.*.product_info_stock_id" => "required|numeric", 
                "packaging_content.*.quantity" => "required|numeric", 
    
            ]);
    
            if ($validation->fails()) {
                return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
            
            }  

            $info = Packaging::find($id);
            $info->category_id = $request->category_id;
            $info->save();

            $deletOld = PackagingContents::where('packaging_id', $id)->delete();
           foreach ($request->packaging_content as $key => $content) {
    
               $addcontent = new PackagingContents;
               $addcontent->packaging_id =  $info->id;
            //    $addRecipe->name = $recipe['name'];
               $addcontent->product_info_stock_id = $content['product_info_stock_id'];
               $addcontent->quantity = $content['quantity'];
               $addcontent->save();
    
           }
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
            
            $info = Packaging::find($id);
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
            
            $info = Packaging::find($id);
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
