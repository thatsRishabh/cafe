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
                        // ->whereNull('parent_id')
                        // ->with('subCategory')
                        ->orderBy('id', 'desc');
                        //  ->orderBy('name', 'asc');
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
            // 'name'                       => 'required|unique:App\Models\Category,name', 
            'image'                  => 'mimes:jpeg,jpg,png,gif|max:10000',
            'name'                      => $nameCheck ? 'required|declined:false' : 'required',
            // 'password_confirmation' => 'required'
        ],
        [
            'name.declined' => 'Name already exists',
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
        //  // file upload format check

        // //  $formatCheck = ['doc','docx','png','jpeg','jpg','pdf','svg','mp4','tif','tiff','bmp','gif','eps','raw','jfif','webp','pem','csv'];
        // $formatCheck = ['png','jpeg','jpg','bmp','webp'];
        //  $extension = strtolower($request->logo->getClientOriginalExtension());
 
        //  if(!in_array($extension, $formatCheck))
        //  {
        //      return prepareResult(false,'file_not_allowed' ,[], 500);
        //  } 
        $info = new Category;

    
         if(!empty($request->image))
         {
           $file=$request->image;
         $filename=time().'.'.$file->getClientOriginalExtension();
         $info->image=env('CDN_DOC_URL').$request->image->move('assets\category_photos',$filename);
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
            // 'name'                       => 'required|unique:App\Models\Category,name'.$id, 
            'name'                      => 'required|unique:categories,name,'.$id,
            // 'image'                         => 'mimes:jpeg,jpg,png,gif|max:10000',
            // 'name'                      => $nameCheck ->name == $request->name ? 'required' : 'required|unique:App\Models\Category,name',
           
        ]);

        if ($validation->fails()) {
            return prepareResult(false,'validation_failed' ,$validation->errors(), 500);
           
        }       
        //  // file upload format check

        // //  $formatCheck = ['doc','docx','png','jpeg','jpg','pdf','svg','mp4','tif','tiff','bmp','gif','eps','raw','jfif','webp','pem','csv'];
        // $formatCheck = ['png','jpeg','jpg','bmp','webp'];
        //  $extension = strtolower($request->logo->getClientOriginalExtension());
 
        //  if(!in_array($extension, $formatCheck))
        //  {
        //      return prepareResult(false,'file_not_allowed' ,[], 500);
        //  } 

                  $info = Category::find($id);
                // if(!empty($request->image))
                // {
                //     if(gettype($request->image) == "string"){
                //         $info->image = $request->image;
                //     }
                //     else{
                //            $file=$request->image;
                //             $filename=time().'.'.$file->getClientOriginalExtension();
                //             $info->image=env('CDN_DOC_URL').$request->image->move('assets\category_photos',$filename);
                //     }
    
                // }
                if(!empty($request->image))
                {
                    if(gettype($request->image) == "string"){
                        $info->image = $request->image;
                    }
                    else{
                        $file=$request->image;
                            $filename=time().'.'.$file->getClientOriginalExtension();
                            $info->image=env('CDN_DOC_URL').$request->image->move('assets\category_photos',$filename);
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

    // public function searchCategory(Request $request)
    // {
    //     try {
    //         $query = Category::select('*')
    //                 // ->whereNull('parent_id')
    //                 // ->with('subCategory')
    //                 ->orderBy('id', 'desc');
    //                 //  ->orderBy('name', 'asc');
    //         if(!empty($request->id))
    //         {
    //             $query->where('id', $request->id);
    //         }
    //         if(!empty($request->name))
    //         {
    //             $query->where('name', $request->name);
    //         }
    //         if(!empty($request->category))
    //         {
    //             $query->where('category', $request->category);
    //         }
    //         if(!empty($request->parent_id))
    //         {
    //             $query->where('id', $request->parent_id);
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

    //         return response(prepareResult(true, $query, trans('Record Fatched Successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
    //     } 
    //     catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('Error while fatching Records')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
    // }

    // public function searchSubcategory(Request $request)
    // {
    //     try {
    //         $query = Category::select('*')
    //             ->whereNotNull('parent_id')
    //             ->orderBy('name', 'asc');
    //         if(!empty($request->id))
    //         {
    //             $query->where('id', $request->id);
    //         }
    //         if(!empty($request->category))
    //         {
    //             // $query->where('category', $request->category);
    //             $query->where('category', 'LIKE', '%'.$request->category.'%');
    //         }
    //         if(!empty($request->parent_id))
    //         {
    //             $query->where('parent_id', $request->parent_id);
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
    //     $validation = Validator::make($request->all(), [
    //         // rahul shanshare asked to implement below 'lte' validation
    //            'name'                       => 'required|unique:App\Models\Category,name',
            
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }
        
    //     DB::beginTransaction();
    //     try {
    //         $info = new Category;

    //         // if(!empty($request->image))
    //         // {
    //         //   $file=$request->image;
    //         // $filename=time().'.'.$file->getClientOriginalExtension();
    //         // $info->image=$request->image->move('assets',$filename);
    //         // }

    //         if(!empty($request->image))
    //         {
    //           $file=$request->image;
    //         $filename=time().'.'.$file->getClientOriginalExtension();
    //         $info->image=imageBaseURL().$request->image->move('assets',$filename);
    //         }

    //         $info->name = $request->name;
    //         // $info->image_url = $request->image_url;
    //         // $info->parent_id = ($request->parent_id) ? $request->parent_id :null;
    //         // $info->is_parent = $request->is_parent;
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
    //     $nameCheck = Category::where('id',  $id)->get('name')->first();
        
    //     $validation = Validator::make($request->all(), [
    //         // 'name'                    => 'required|unique:categories,name',
    //         'name'                      => $nameCheck ->name == $request->name ? 'required' : 'required|unique:App\Models\Category,name',
            
           
    //     ]);

    //     if ($validation->fails()) {
    //         return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $info = Category::find($id);

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
    //         // $info->image_url = $request->image_url;
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
            
    //         $info = Category::find($id);
    //         if($info)
    //         {
    //             // return response(prepareResult(false, $info, trans('translate.fetched_records')), config('httpcodes.success'));
    //             return response(prepareResult(true, $info, trans('Record Featched Successfully')), 200 , ['Result'=>'httpcodes.found']);
    //         }
    //         return response(prepareResult(false, [], trans('Error while featching Records')),500,  ['Result'=>'httpcodes.not_found']);
    //     } catch (\Throwable $e) {
    //         Log::error($e);
    //         return response()->json(prepareResult(false, $e->getMessage(), trans('translate.something_went_wrong')), 500,  ['Result'=>'httpcodes.internal_server_error']);
    //     }
    // }

    // public function destroy($id)
    // {
    //     try {
            
    //         $info = Category::find($id);
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
