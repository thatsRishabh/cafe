<?php

namespace App\Http\Controllers\Api\Common;

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

            return response(prepareResult(true, $query, trans('Record Fatched Successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
        } 
        catch (\Throwable $e) {
            Log::error($e);
            return response()->json(prepareResult(false, $e->getMessage(), trans('Error while fatching Records')), 500,  ['Result'=>'Your data has not been saved']);
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
            // rahul shanshare asked to implement below 'lte' validation
               'name'                       => 'required|unique:App\Models\Category,name',
            
           
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
        }
        
        DB::beginTransaction();
        try {
            $info = new Category;

            // if(!empty($request->image))
            // {
            //   $file=$request->image;
            // $filename=time().'.'.$file->getClientOriginalExtension();
            // $info->image=$request->image->move('assets',$filename);
            // }

            if(!empty($request->image))
            {
              $file=$request->image;
            $filename=time().'.'.$file->getClientOriginalExtension();
            $info->image=imageBaseURL().$request->image->move('assets',$filename);
            }

            $info->name = $request->name;
            // $info->image_url = $request->image_url;
            // $info->parent_id = ($request->parent_id) ? $request->parent_id :null;
            // $info->is_parent = $request->is_parent;
            $info->save();
            DB::commit();
            return response()->json(prepareResult(true, $info, trans('Your data has been saved successfully')), 200 , ['Result'=>'Your data has been saved successfully']);
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollback();
            return response()->json(prepareResult(false, $e->getMessage(), trans('Your data has not been saved')), 500,  ['Result'=>'Your data has not been saved']);
        }
    }

    public function update(Request $request, $id)
    {
        $nameCheck = Category::where('id',  $id)->get('name')->first();
        
        $validation = Validator::make($request->all(), [
            // 'name'                    => 'required|unique:categories,name',
            'name'                      => $nameCheck ->name == $request->name ? 'required' : 'required|unique:App\Models\Category,name',
            
           
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->errors(), trans('validation_failed')), 500,  ['Result'=>'Your data has not been saved']);
        }

        DB::beginTransaction();
        try {
            $info = Category::find($id);

            if(!empty($request->image))
            {
                if(gettype($request->image) == "string"){
                    $info->image = $request->image;
                }
                else{
                       $file=$request->image;
                        $filename=time().'.'.$file->getClientOriginalExtension();
                        $info->image=imageBaseURL().$request->image->move('assets',$filename);
                }

            //   $file=$request->image;
            // $filename=time().'.'.$file->getClientOriginalExtension();
            // $info->image=imageBaseURL().$request->image->move('assets',$filename);
            }

            $info->name = $request->name;
            // $info->image_url = $request->image_url;
            // $info->parent_id = ($request->parent_id) ? $request->parent_id :null;
            // $info->is_parent = $request->is_parent;
            $info->save();
            DB::commit();
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
            
            $info = Category::find($id);
            if($info)
            {
                // return response(prepareResult(false, $info, trans('translate.fetched_records')), config('httpcodes.success'));
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
            
            $info = Category::find($id);
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
