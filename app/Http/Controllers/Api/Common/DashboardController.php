<?php

namespace App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ProductMenu;
use App\Models\Order;
use App\Models\Employee;
use App\Models\User;
use App\Models\AttendenceList;
use App\Models\OrderContain;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



class DashboardController extends Controller
{
    public function dashboard()
    {
        try {
            $data = [];
          
            // $data['todaySale'] = DB::table('orders')->whereDate('orders.created_at', '=', date("Y-m-d"))->sum('netAmount');
            $data['todaySale'] = Order::whereDate('orders.created_at', '=', date("Y-m-d"))->sum('netAmount');
            $data['employeePresentToday'] = AttendenceList::where('attendence',2)->whereDate('created_at', '=', date("Y-m-d"))->count();
            $data['employeeHalfDayToday'] = AttendenceList::where('attendence',3)->whereDate('created_at', '=', date("Y-m-d"))->count();
            $data['totalEmployee'] = User::where('role_id', 3)->count();

           
            return prepareResult(true,'Record Fatched Successfully' ,$data, 200);
            } 
            catch (\Throwable $e) {
                Log::error($e);
                return prepareResult(false,'Error while fatching Records' ,$e->getMessage(), 500);
            }
    }

    public function categoryWiseList(Request $request)
    {
        try {
            
        $data = getDetails($request->start_date, $request->end_date, $request->category, $request->cafe_id);
        return prepareResult(true,'Record Fatched Successfully' ,$data, 200);
    } 
    catch (\Throwable $e) {
        Log::error($e);
        return prepareResult(false,'Error while fatching Records' ,$e->getMessage(), 500);
    }
        
    }
    public function dashboardGraph(Request $request){
        try {
            $data = [];
            // $data['category_name'] =getCategoryName($request->categoryDay);
            // $data['category_quantity'] =getCategoryQuantity($request->categoryDay);
            // $data['total_name'] =getLast30TotalName($request->day , $request->startDate, $request->endDate,  $request->category);
            $data['total_sale'] =getLast30TotalSale($request->day , $request->startDate, $request->endDate,  $request->subcategory, $request->cafe_id);
            $data['total_sale_online'] =getLast30TotalOnlineSale($request->day , $request->startDate, $request->endDate,  $request->subcategory, $request->cafe_id);
            $data['total_sale_cash'] =getLast30TotalCashSale($request->day , $request->startDate, $request->endDate,  $request->subcategory, $request->cafe_id);
            $data['total_sale_recurring'] =getLast30TotalRecurringSale($request->day , $request->startDate, $request->endDate,  $request->subcategory, $request->cafe_id);
            // // $data['total_customer'] =getLast30TotalCustomer();
            $data['total_product'] =getLast30TotalProduct($request->day , $request->startDate, $request->endDate,  $request->subcategory, $request->cafe_id);
            $data['total_expense'] =getLast30TotalExpense($request->day , $request->startDate, $request->endDate, $request->cafe_id);
            $data['labels'] =getLast30DaysList($request->day , $request->startDate, $request->endDate);

            return prepareResult(true,'Record Fatched Successfully' ,$data, 200);
        } 
        catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'Error while fatching Records' ,$e->getMessage(), 500);
        }

    }

    public function dashboardGraphByName(Request $request){
        try {
            // $data = [];
            $data = getLast30details($request->day , $request->startDate, $request->endDate, $request->cafe_id);

            return prepareResult(true,'Record Fatched Successfully' ,$data, 200);
            
        } 
        catch (\Throwable $e) {
            Log::error($e);
            return prepareResult(false,'Error while fatching Records' ,$e->getMessage(), 500);
            
        }

    }

}
