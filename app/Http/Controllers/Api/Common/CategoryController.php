<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{

    public function searchCategory(Request $request)
    {
        try {

        $query = Category::select('*')
                        ->orderBy('id', 'desc');
                if(!empty($request->id))
                {
                    $query->where('id', $request->id);
                }
                if(!empty($request->name))
                {
                    $query->where('name', $request->name);
                }
                if(!empty($request->category))
                {
                    $query->where('category', $request->category);
                }
                if(!empty($request->parent_id))
                {
                    $query->where('id', $request->parent_id);
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

    public function searchSubcategory(Request $request)
    {
        try {
            $query = Category::select('*')
                ->whereNotNull('parent_id')
                ->orderBy('name', 'asc');
            if(!empty($request->id))
            {
                $query->where('id', $request->id);
            }
            if(!empty($request->category))
            {
                // $query->where('category', $request->category);
                $query->where('category', 'LIKE', '%'.$request->category.'%');
            }
            if(!empty($request->parent_id))
            {
                $query->where('parent_id', $request->parent_id);
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
        $nameCheck = Category::where('name', $request->name)->first();

        DB::beginTransaction();
        try {
        $validation = Validator::make($request->all(),  [
            'image'                       => $request->image ? 'mimes:jpeg,jpg,png,gif|max:10000' : '',
            'name'                      => $nameCheck ? 'required|declined:false' : 'required',
        ],
        [
            'name.declined' => 'Name already exists',
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
    
        $info = new Category;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename=time().'.'.$file->getClientOriginalExtension();
            if ($file->move('assets/category_photos', $filename)) {
                $info->image=env('CDN_DOC_URL').'assets/category_photos/'.$filename.'';
            }
           }
          $info->name = $request->name;
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
        $nameCheck = Category::where('id',  $id)->get('name')->first();

        $validation = Validator::make($request->all(), [
            'name'                      => 'required|unique:categories,name,'.$id,
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
                  $info = Category::find($id);
                if(!empty($request->image))
                {
                    if(gettype($request->image) == "string"){
                        $info->image = $request->image;
                    }
                    else{
                        if ($request->hasFile('image')) {
                            $file = $request->file('image');
                            $filename=time().'.'.$file->getClientOriginalExtension();
                            if ($file->move('assets/category_photos', $filename)) {
                                $info->image=env('CDN_DOC_URL').'assets/category_photos/'.$filename.'';
                            }
                           }
                    }
                }
                $info->name = $request->name;
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
            
            $info = Category::find($id);
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
            
            $info = Category::find($id);
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
