<?php

use App\Models\Unit;
use App\Models\ProductStockManage;
use App\Models\ProductMenu;
use App\Models\ProductInfo;
use App\Models\EmployeeAttendence;
use App\Models\AttendenceList;
use App\Models\Category;
use App\Models\OrderContain;
use App\Models\Expense;
use App\Models\Order;
use App\Models\RecipeContains;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;



    function prepareResult($status, $message, $payload, $status_code)
    {
            if(empty($payload)) {
                $payload = new stdClass();
            } else {
                $payload = $payload;
            }
            return response()->json(['success' => $status, 'message' => $message, 'payload' => $payload, 'code' => $status_code],$status_code);
        
    }

      
	// function getUser() {
	// 	return auth('api')->user();
	// }


    function unitConversion($unitID, $quantity) {

		$unitName = Unit::where('id', $unitID)->get('name')->first();
		// return $unitName->name;
	
			if((strtolower($unitName->name) == "kilogram") || (strtolower($unitName->name) == "liter") || (strtolower($unitName->name) == "litre"))
			{
				$value = $quantity*1000;
				return $value;
			}
			if ((strtolower($unitName->name) == "gram") || (strtolower($unitName->name) == "millilitre") || (strtolower($unitName->name) == "pack") || (strtolower($unitName->name) == "piece")) 
			{
				$value = $quantity;
				return $value;
			}
			if ((strtolower($unitName->name) == "dozen")) 
			{
				$value = $quantity*12;
				return $value;
			}

	}

	function unitSimilarTypeCheck($unitID, $product_stock_id) {

		$unitName = Unit::where('id', $unitID)->get('name')->first();
		$productUnitID = ProductInfo::where('id', $product_stock_id)->get('unit_id')->first();
		$productUnitName = Unit::where('id', $productUnitID->unit_id)->get('name')->first();
		
		if($unitID != $productUnitID->unit_id)
		{
			
			if((strtolower($productUnitName->name) == "kilogram") && (strtolower($unitName->name) == "gram"))
			{
				return 'required';
			}

			if((strtolower($productUnitName->name) == "gram") && (strtolower($unitName->name) == "kilogram"))
			{
				return 'required';
			}
			if((strtolower($productUnitName->name) == "millilitre") && (strtolower($unitName->name) == "liter"))
			{
				return 'required';
			}

			if((strtolower($productUnitName->name) == "liter") && (strtolower($unitName->name) == "millilitre"))
			{
				return 'required';
			}
			
			if((strtolower($productUnitName->name) == "dozen") && (strtolower($unitName->name) == "piece"))
			{
				return 'required';
			}

			if((strtolower($productUnitName->name) == "piece") && (strtolower($unitName->name) == "dozen"))
			{
				return 'required';
			}
			else
            {
                return 'required|declined:false';
            }
		}
		else
       {
           return 'required';
       }
	}



	function getUser() {
		return auth('api')->user();
	}

	// function getLast30TotalName($day, $startDate , $endDate, $category)
	// 	{
	// 		if(!empty($day))
    //         {

	// 			$today     = new \DateTime();
	// 			// $begin     = $today->sub(new \DateInterval('P30D'));
	
	// 			if(($day == 1 ))
	// 			{
	// 				$begin = $today->sub(new \DateInterval('P0D'));
	// 			}
	// 			elseif (($day == 7)) 
	// 			{
	// 				$begin= $today->sub(new \DateInterval('P7D'));
	// 			}
	// 			elseif (($day == 30 )) 
	// 			{
	// 				$begin= $today->sub(new \DateInterval('P30D'));
	// 			}
	
	// 			$end       = new \DateTime();
	// 			$end       = $end->modify('+1 day');
	// 			$interval  = new \DateInterval('P1D');
	// 			$daterange = new \DatePeriod($begin, $interval, $end);
	// 			$totalSale =[];
	// 			foreach ($daterange as $date) {
					
	// 			$salesSum = OrderContain::where('category_id',$category)->whereDate('created_at',$date->format('Y-m-d'))->get('name'); 
					
	// 				$totalSale[] = $salesSum;
	// 			}
	// 		}

		
		// 	if(!empty( $startDate))
        //     {

		// 		$rangArray = []; 
		// 		$startDate = strtotime($startDate);
		// 		$endDate = strtotime($endDate);
					
		// 			for ($currentDate = $startDate; $currentDate <= $endDate; 
		// 											$currentDate += (86400)) {
															
		// 				$date = date('Y-m-d', $currentDate);
		// 				$rangArray[] = $date;
		// 			}
  			
		// 		$totalSale =[];
		// 		foreach ($rangArray as $date) {
					
		// 			$salesSum = OrderContain::where('category_id',$category)->whereDate('created_at',$date->format('Y-m-d'))->sum('netPrice');
					
		// 			$totalSale[] = $salesSum;
		// 		}
		
		// 	}
		
		

		// 	// $data = implode(', ', $totalSale);
		// 	return $totalSale;
		// }

	
		function getLast30TotalSale($day, $startDate , $endDate, $subcategory)
		{
			if(!empty($day))
            {

				$today     = new \DateTime();
				// $begin     = $today->sub(new \DateInterval('P30D'));
	
				if(($day == 1 ))
				{
					$begin = $today->sub(new \DateInterval('P0D'));
				}
				elseif (($day == 7)) 
				{
					$begin= $today->sub(new \DateInterval('P7D'));
				}
				elseif (($day == 30 )) 
				{
					$begin= $today->sub(new \DateInterval('P30D'));
				}
	
				$end       = new \DateTime();
				$end       = $end->modify('+1 day');
				$interval  = new \DateInterval('P1D');
				$daterange = new \DatePeriod($begin, $interval, $end);
				$totalSale =[];
				foreach ($daterange as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->select('id')->get();
				$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice'); 
					}
					else{
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
			}

		
			if(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $startDate; $currentDate <= $endDate; 
													$currentDate += (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				$totalSale =[];
				foreach ($rangArray as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->select('id')->get();
					$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					else{
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
		
			}
		
		

			// $data = implode(', ', $totalSale);
			return $totalSale;
		}

		function getLast30TotalOnlineSale($day, $startDate , $endDate, $subcategory)
		{
			if(!empty($day))
            {

				$today     = new \DateTime();
				// $begin     = $today->sub(new \DateInterval('P30D'));
	
				if(($day == 1 ))
				{
					$begin = $today->sub(new \DateInterval('P0D'));
				}
				elseif (($day == 7)) 
				{
					$begin= $today->sub(new \DateInterval('P7D'));
				}
				elseif (($day == 30 )) 
				{
					$begin= $today->sub(new \DateInterval('P30D'));
				}
	
				$end       = new \DateTime();
				$end       = $end->modify('+1 day');
				$interval  = new \DateInterval('P1D');
				$daterange = new \DatePeriod($begin, $interval, $end);
				$totalSale =[];
				foreach ($daterange as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->where('mode_of_transaction', 2)->select('id')->get();
				$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice'); 
					}
					else{
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->where('mode_of_transaction', 2)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
			}

		
			if(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $startDate; $currentDate <= $endDate; 
													$currentDate += (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				$totalSale =[];
				foreach ($rangArray as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 2)->select('id')->get();
					$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					else{
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 2)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
		
			}
		
		

			// $data = implode(', ', $totalSale);
			return $totalSale;
		}

		function getLast30TotalCashSale($day, $startDate , $endDate, $subcategory)
		{
			if(!empty($day))
            {

				$today     = new \DateTime();
				// $begin     = $today->sub(new \DateInterval('P30D'));
	
				if(($day == 1 ))
				{
					$begin = $today->sub(new \DateInterval('P0D'));
				}
				elseif (($day == 7)) 
				{
					$begin= $today->sub(new \DateInterval('P7D'));
				}
				elseif (($day == 30 )) 
				{
					$begin= $today->sub(new \DateInterval('P30D'));
				}
	
				$end       = new \DateTime();
				$end       = $end->modify('+1 day');
				$interval  = new \DateInterval('P1D');
				$daterange = new \DatePeriod($begin, $interval, $end);
				$totalSale =[];
				foreach ($daterange as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->where('mode_of_transaction', 1)->select('id')->get();
				$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice'); 
					}
					else{
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->where('mode_of_transaction', 1)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
			}

		
			if(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $startDate; $currentDate <= $endDate; 
													$currentDate += (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				$totalSale =[];
				foreach ($rangArray as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 1)->select('id')->get();
					$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					else{
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 1)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
		
			}
		
		

			// $data = implode(', ', $totalSale);
			return $totalSale;
		}

		function getLast30TotalRecurringSale($day, $startDate , $endDate, $subcategory)
		{
			if(!empty($day))
            {

				$today     = new \DateTime();
				// $begin     = $today->sub(new \DateInterval('P30D'));
	
				if(($day == 1 ))
				{
					$begin = $today->sub(new \DateInterval('P0D'));
				}
				elseif (($day == 7)) 
				{
					$begin= $today->sub(new \DateInterval('P7D'));
				}
				elseif (($day == 30 )) 
				{
					$begin= $today->sub(new \DateInterval('P30D'));
				}
	
				$end       = new \DateTime();
				$end       = $end->modify('+1 day');
				$interval  = new \DateInterval('P1D');
				$daterange = new \DatePeriod($begin, $interval, $end);
				$totalSale =[];
				foreach ($daterange as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->where('mode_of_transaction', 3)->select('id')->get();
				$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice'); 
					}
					else{
						$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->where('mode_of_transaction', 3)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
			}

		
			if(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $startDate; $currentDate <= $endDate; 
													$currentDate += (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				$totalSale =[];
				foreach ($rangArray as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 3)->select('id')->get();
					$salesSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					else{
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 3)->select('id')->get();
						$salesSum = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					}
					$totalSale[] = $salesSum;
				}
		
			}
		
		

			// $data = implode(', ', $totalSale);
			return $totalSale;
		}

	// function getLast30TotalCustomer($day)
	// 	{
	// 		$today     = new \DateTime();
	// 		// $begin     = $today->sub(new \DateInterval('P30D'));

	// 		if(($day == 1 ))
	// 		{
	// 			$begin = $today->sub(new \DateInterval('P0D'));
	// 		}
	// 		elseif (($day == 7)) 
	// 		{
	// 			$begin= $today->sub(new \DateInterval('P7D'));
	// 		}
	// 		elseif (($day == 30 )) 
	// 		{
	// 			$begin= $today->sub(new \DateInterval('P30D'));
	// 		}

	// 		$end       = new \DateTime();
	// 		$end       = $end->modify('+1 day');
	// 		$interval  = new \DateInterval('P1D');
	// 		$daterange = new \DatePeriod($begin, $interval, $end);
	// 		$totalCustomer =[];
	// 		foreach ($daterange as $date) {
				
	// 		$customer = Order::whereDate('created_at',$date->format('Y-m-d'))->count(); 
				
	// 			$totalCustomer[] = $customer;
	// 		}
		

	// 		// $data = implode(', ', $totalCustomer);
	// 		return $totalCustomer;
	// 	}

	function getLast30TotalProduct($day, $startDate , $endDate, $subcategory)
		{
			if(!empty($day))
            {
				$today     = new \DateTime();
				// $begin     = $today->sub(new \DateInterval('P30D'));
	
				if(($day == 1 ))
				{
					$begin = $today->sub(new \DateInterval('P0D'));
				}
				elseif (($day == 7)) 
				{
					$begin= $today->sub(new \DateInterval('P7D'));
				}
				elseif (($day == 30 )) 
				{
					$begin= $today->sub(new \DateInterval('P30D'));
				}
	
				$end       = new \DateTime();
				$end       = $end->modify('+1 day');
				$interval  = new \DateInterval('P1D');
				$daterange = new \DatePeriod($begin, $interval, $end);
				$totalProduct =[];
				foreach ($daterange as $date) {
				if(!empty( $subcategory)){
					$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->select('id')->get();

				$productSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('quantity'); 
				}
				else{
					$orderid = Order::whereDate('created_at',$date->format('Y-m-d'))->where('order_status', 2)->select('id')->get();
					$productSum = OrderContain::whereDate('created_at',$date->format('Y-m-d'))->whereIn('order_id',$orderid)->sum('quantity');
					// $productSum = OrderContain::whereDate('created_at',$date->format('Y-m-d'))->sum('quantity'); 

				}
					$totalProduct[] = $productSum;
				}
			
		
			}

			if(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $startDate; $currentDate <= $endDate; 
													$currentDate += (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				$totalProduct =[];
				foreach ($rangArray as $date) {
					if(!empty( $subcategory)){
						$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->select('id')->get();
					$productSum = OrderContain::where('product_menu_id',$subcategory)->whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('quantity'); 
				}
				else{

					$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->select('id')->get();
					$productSum = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('quantity');
					// $productSum = OrderContain::whereDate('created_at',$date)->sum('quantity'); 
				}
					$totalProduct[] = $productSum;
				}
		
			}
		

			// $data = implode(', ', $totalProduct);
			return $totalProduct;
		}

	function getLast30TotalExpense($day, $startDate , $endDate)
		{
			if(!empty($day))
            {

						$today     = new \DateTime();
					// // $begin     = $today->sub(new \DateInterval('P30D'));

					if(($day == 1 ))
					{
						$begin = $today->sub(new \DateInterval('P0D'));
					}
					elseif (($day == 7)) 
					{
						$begin= $today->sub(new \DateInterval('P7D'));
					}
					elseif (($day == 30 )) 
					{
						$begin= $today->sub(new \DateInterval('P30D'));
					}

					$end       = new \DateTime();
					$end       = $end->modify('+1 day');
					$interval  = new \DateInterval('P1D');
					$daterange = new \DatePeriod($begin, $interval, $end);
					
					$totalExpense =[];
					foreach ($daterange as $date) {
						
						$expenseSum = Expense::whereDate('expense_date',$date->format('Y-m-d'))->sum('totalExpense'); 
						
						$totalExpense[] = $expenseSum;
					}

			}

			if(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $startDate; $currentDate <= $endDate; 
													$currentDate += (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				$totalExpense =[];
				foreach ($rangArray as $date) {
					
					$expenseSum = Expense::whereDate('expense_date',$date)->sum('totalExpense'); 
					
					$totalExpense[] = $expenseSum;
				}
		
			}

			// $data = implode(', ', $totalRevenue);
			return $totalExpense;

		}


	function getLast30DaysList($day, $startDate , $endDate)
		{
			if(!empty($day))
            {
				$today     = new \DateTime();
				// $begin     = $today->sub(new \DateInterval('P0D'));
				
				if(($day == 1 ))
				{
					$begin = $today->sub(new \DateInterval('P0D'));
				}
				elseif (($day == 7)) 
				{
					$begin= $today->sub(new \DateInterval('P7D'));
				}
				elseif (($day == 30 )) 
				{
					$begin= $today->sub(new \DateInterval('P30D'));
				}
				$end       = new \DateTime();
				$end       = $end->modify('+1 day');
				$interval  = new \DateInterval('P1D');
				$daterange = new \DatePeriod($begin, $interval, $end);
				foreach ($daterange as $date) {
					$dateList[] = ''.$date->format("Y-m-d").'';
				}
            }

			if(!empty( $startDate))
            {
				$dateList = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
             
				for ($currentDate = $startDate; $currentDate <= $endDate; 
												$currentDate += (86400)) {
														
					$date = date('Y-m-d', $currentDate);
					$dateList[] = $date;
				}
		
			}
			
			return $dateList;
		}

		function getCategoryName($categoryDay)
		{
			// if(!empty($categoryDay))
            // {

			// 			$today     = new \DateTime();
			// 		// // $begin     = $today->sub(new \DateInterval('P30D'));

			// 		if(($categoryDay == 1 ))
			// 		{
			// 			$begin = $today->sub(new \DateInterval('P0D'));
			// 		}
			// 		elseif (($categoryDay == 7)) 
			// 		{
			// 			$begin= $today->sub(new \DateInterval('P7D'));
			// 		}
			// 		elseif (($categoryDay == 30 )) 
			// 		{
			// 			$begin= $today->sub(new \DateInterval('P30D'));
			// 		}

			// 		$end       = new \DateTime();
			// 		$end       = $end->modify('+1 day');
			// 		$interval  = new \DateInterval('P1D');
			// 		$daterange = new \DatePeriod($begin, $interval, $end);
					
			// 		// $tree = User::where('branch_id',$branch_id)->pluck('id')
			// 		// ->toArray();
			// 		$catogoryID =[];
			// 		foreach ($daterange as $date) {
						
			// 			// $categorySearch= OrderContain::whereDate('created_at',$date->format('Y-m-d'))->pluck('category_id');
			// 			$categorySearch= OrderContain::whereDate('created_at',$date->format('Y-m-d'))->get('category_id')->unique();
						
			// 			$catogoryID[] = $categorySearch;
			// 		}

			// 		// $catogoryName=[];
			// 		// foreach ($catogoryID as $category) {
						
			// 		// 	$catogoryName[]= Category::where('id',$category)->pluck('name')->first();
						
				
			// 		// }
					
			// }
			
 
			// $unique = $catogoryID->unique();
			 
			// $unique->values()->all();

			// $data = implode(', ', $totalRevenue);

			

			$categorySearch= DB::table("order_contains")->select('category_id')->whereDate('created_at','>', now()->subDays(30)->endOfDay())->unique()->all();

			// $users = DB::table("users")
			// 	->select('id')
			// 	->where('accounttype', 'standard')
			// 	->where('created_at', '>', now()->subDays(30)->endOfDay())
			// 	->all();	
			return $categorySearch;
		}

	function getDetails($startDate , $endDate, $category)
	{
		if(!empty($startDate))
		{
			if(!empty($category)){

			
				$date = Carbon::createFromFormat('Y-m-d', $endDate);
				// $daysToAdd = 1;
				$date = $date->addDays(1);
				$a = 	DB::table('order_contains as w')
				->join("product_menus", "w.product_menu_id", "=", "product_menus.id")
				->where('w.category_id', $category)
				->whereBetween('w.created_at', [$startDate, date_format($date, "Y-m-d")])
				// ->whereBetween('w.created_at', ["2022-07-26", "2022-08-26"])
				->select(array(DB::Raw('sum(w.quantity) as total_quantity'), DB::Raw('sum(w.netPrice) as total_netPrice'), DB::Raw('DATE(w.created_at) date'), 'w.product_menu_id', 'product_menus.name'))
				->groupBy(['date', 'w.product_menu_id', 'product_menus.name'])
				->orderBy('w.created_at', 'desc')
				->get();
				$orderDetails = $a;
				return  $orderDetails;
			}else{
				$date = Carbon::createFromFormat('Y-m-d', $endDate);
				// $daysToAdd = 1;
				$date = $date->addDays(1);
				$a = 	DB::table('order_contains as w')
				->join("product_menus", "w.product_menu_id", "=", "product_menus.id")
				// ->where('w.category_id', $category)
				->whereBetween('w.category_id', [1, 1000])
				->whereBetween('w.created_at', [$startDate, date_format($date, "Y-m-d")])
				// ->whereBetween('w.created_at', ["2022-07-26", "2022-08-26"])
				->select(array(DB::Raw('sum(w.quantity) as total_quantity'), DB::Raw('sum(w.netPrice) as total_netPrice'), DB::Raw('DATE(w.created_at) date'), 'w.product_menu_id', 'product_menus.name'))
				->groupBy(['date', 'w.product_menu_id', 'product_menus.name'])
				->orderBy('w.created_at', 'desc')
				->get();
				$orderDetails = $a;
				return  $orderDetails;

			}

		}
		elseif(!empty($category)){
			$startDate = date('Y-m-d');
			// echo $startDate;
			$toDay = Carbon::createFromFormat('Y-m-d', $startDate);
            $daysToAdd = 1;
            $toDay = $toDay->addDays($daysToAdd);
			$enddate = Carbon::createFromFormat('Y-m-d', $startDate);
            $daysToAdd = -30;
            $enddate = $enddate->addDays($daysToAdd);
			$a = 	DB::table('order_contains as w')
			->join("product_menus", "w.product_menu_id", "=", "product_menus.id")
			->where('w.category_id', $category)
			->whereBetween('w.created_at', [date_format($enddate, "Y-m-d"), date_format($toDay, "Y-m-d")])
			->select(array(DB::Raw('sum(w.quantity) as total_quantity'), DB::Raw('sum(w.netPrice) as total_netPrice'), DB::Raw('DATE(w.created_at) date'), 'w.product_menu_id', 'product_menus.name'))
			->groupBy(['date', 'w.product_menu_id', 'product_menus.name'])
            ->orderBy('w.created_at', 'desc')
            ->get();
			$orderDetails = $a;
			return  $orderDetails;
		}
		else{
			$startDate = date('Y-m-d');
			// $startDate = "2022-09-25";
			// echo $startDate;
			$toDay = Carbon::createFromFormat('Y-m-d', $startDate);
            $daysToAdd = 1;
            $toDay = $toDay->addDays($daysToAdd);
			$enddate = Carbon::createFromFormat('Y-m-d', $startDate);
            $daysToAdd = -30;
            $enddate = $enddate->addDays($daysToAdd);
			$a = 	DB::table('order_contains as w')
			->join("product_menus", "w.product_menu_id", "=", "product_menus.id")
			->whereBetween('w.category_id', [1, 1000])
			->whereBetween('w.created_at', [date_format($enddate, "Y-m-d"), date_format($toDay, "Y-m-d")])
			->select(array(DB::Raw('sum(w.quantity) as total_quantity'), DB::Raw('sum(w.netPrice) as total_netPrice'), DB::Raw('DATE(w.created_at) date'), 'w.product_menu_id', 'product_menus.name'))
			->groupBy(['date', 'w.product_menu_id', 'product_menus.name'])
            ->orderBy('w.created_at', 'desc')
            ->get();
			$orderDetails = $a;
			return  $orderDetails;
			
		}

		
	}
	function getLast30TotalSales($day, $startDate , $endDate)
		{
			if(!empty($day))
            {

						$today     = new \DateTime();
					// // $begin     = $today->sub(new \DateInterval('P30D'));

					if(($day == 1 ))
					{
						$begin = $today->sub(new \DateInterval('P0D'));
					}
					elseif (($day == 7)) 
					{
						$begin= $today->sub(new \DateInterval('P7D'));
					}
					elseif (($day == 30 )) 
					{
						$begin= $today->sub(new \DateInterval('P30D'));
					}

					$end       = new \DateTime();
					$end       = $end->modify('+1 day');
					$interval  = new \DateInterval('P1D');
					$daterange = new \DatePeriod($begin, $interval, $end);
					
					$totalRevenue =[];
					foreach ($daterange as $date) {
						
						$salesSum = Order::whereDate('created_at',$date->format('Y-m-d'))->sum('netAmount'); 
						
						$totalSale[] = $salesSum;
					}

			}

			if(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $startDate; $currentDate <= $endDate; 
													$currentDate += (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				$totalRevenue =[];
				foreach ($rangArray as $date) {
					
					$salesSum = Order::whereDate('created_at',$date)->sum('netAmount'); 
					
					$totalSale[] = $salesSum;
				}
		
			}
			
		

			// $data = implode(', ', $totalRevenue);
			return $totalSale;
		}

		function getLast30details($day, $startDate , $endDate)
		{
			if(!empty($day))
            {
				$rangArray = []; 
				$startDate = date('Y-m-d');
				$toDay = Carbon::createFromFormat('Y-m-d', $startDate);
            	$daysToAdd = 0;
				$toDay = $toDay->addDays($daysToAdd);
				// return $toDay;
				$toDay = strtotime($toDay);
				// return $toDay;
				$endDay = Carbon::createFromFormat('Y-m-d', $startDate);
            	$daysToAdd = -($day-1);
            	$endDay = $endDay->addDays($daysToAdd);
				// return $endDate;
				$endDate = strtotime($endDay);
				// return $endDate;
				
					
					for ($currentDate = $toDay; $currentDate >= $endDate; 
													$currentDate -= (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				// $orderDetails =[];
				foreach ($rangArray as $date) {
					$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->select('id')->get();
					$orderidCash = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 1)->select('id')->get();
					$orderidOnline = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 2)->select('id')->get();
					$orderidrecurring = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 3)->select('id')->get();

					$orders['date']= $date;
					$orders['sales_online']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidOnline)->sum('netPrice');
					$orders['sales_cash']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidCash)->sum('netPrice');
					$orders['sales_recurring']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidrecurring)->sum('netPrice');
					$orders['sales']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					$orders['product'] = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('quantity'); 
					$orders['expense']= Expense::whereDate('expense_date',$date)->sum('totalExpense');
					$orderDetails[] = $orders;
					
				}
				return $orderDetails;
			
		
			}
			// $expenseSum = Expense::whereDate('expense_date',$date->format('Y-m-d'))->sum('totalExpense'); 
			elseif(!empty( $startDate))
            {

				$rangArray = []; 
				$startDate = strtotime($startDate);
				$endDate = strtotime($endDate);
					
					for ($currentDate = $endDate; $currentDate >= $startDate; 
													$currentDate -= (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				// $orderDetails =[];
				foreach ($rangArray as $date) {
					$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->select('id')->get();
					$orderidCash = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 1)->select('id')->get();
					$orderidOnline = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 2)->select('id')->get();
					$orderidrecurring = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 3)->select('id')->get();

					$orders['date']= $date;
					$orders['sales_online']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidOnline)->sum('netPrice');
					$orders['sales_cash']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidCash)->sum('netPrice');
					$orders['sales_recurring']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidrecurring)->sum('netPrice');
					$orders['sales']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					$orders['product'] = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('quantity'); 
					$orders['expense']= Expense::whereDate('expense_date',$date)->sum('totalExpense');
					$orderDetails[] = $orders;
					
				}
				return $orderDetails;
			}
			else{
				$rangArray = []; 
				$startDate = date('Y-m-d');
				$toDay = Carbon::createFromFormat('Y-m-d', $startDate);
            	$daysToAdd = 0;
				$toDay = $toDay->addDays($daysToAdd);
				// return $toDay;
				$toDay = strtotime($toDay);
				// return $toDay;
				$endDay = Carbon::createFromFormat('Y-m-d', $startDate);
            	$daysToAdd = -(7);
            	$endDay = $endDay->addDays($daysToAdd);
				// return $endDate;
				$endDate = strtotime($endDay);
				// return $endDate;
				
					
					for ($currentDate = $toDay; $currentDate >= $endDate; 
													$currentDate -= (86400)) {
															
						$date = date('Y-m-d', $currentDate);
						$rangArray[] = $date;
					}
  			
				// $orderDetails =[];
				foreach ($rangArray as $date) {
					$orderid = Order::whereDate('created_at',$date)->where('order_status', 2)->select('id')->get();
					$orderidCash = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 1)->select('id')->get();
					$orderidOnline = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 2)->select('id')->get();
					$orderidrecurring = Order::whereDate('created_at',$date)->where('order_status', 2)->where('mode_of_transaction', 3)->select('id')->get();

					$orders['date']= $date;
					$orders['sales_online']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidOnline)->sum('netPrice');
					$orders['sales_cash']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidCash)->sum('netPrice');
					$orders['sales_recurring']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderidrecurring)->sum('netPrice');
					$orders['sales']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					$orders['product'] = OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('quantity'); 
					$orders['revenue']= OrderContain::whereDate('created_at',$date)->whereIn('order_id',$orderid)->sum('netPrice');
					$orderDetails[] = $orders;
					
				}
				return $orderDetails;
			
				}
			
			
			// $data = implode(', ', $totalProduct);
		}

		// function recipeDeductionValidation($productID, $quantity)
		// {
			
		// 	$deletOld  = RecipeContains::where('recipe_id', $productID)->get();
		// 	$recipeStock = []; 
		// 	foreach ($deletOld as $key => $value) {
					
				
		// 		$updateStock = ProductInfo::find($value->product_info_stock_id);

		// 		   $currentQuanitity =  $updateStock->current_quanitity - (unitConversion($value->unit_id, $value->quantity) * $quantity );
		// 			$validation1 = $currentQuanitity < 0 ? 1 : '';
		// 			return $validation1;
				
		// 		}
			
		// 	// return $recipeStock;
		// }

		function recipeDeductionValidation($productID, $quantity)
		{
			
			$deletOld  = RecipeContains::where('recipe_id', $productID)->get();
			// return $deletOld;
			$recipeStock = []; 
			foreach ($deletOld as $key => $value) {
					
				
				$updateStock = ProductInfo::find($value->product_info_stock_id);

				   $lessQuantity=  $updateStock->current_quanitity - (unitConversion($value->unit_id, $value->quantity) * $quantity );

					// return $lessQuantity;
				   if($lessQuantity < 0)
				   {
					return 'required|declined:false';
				   }
				   else
				   {
					return 'required';
				   }
			}
			
			// return "hello";
		}

		function recipeDeduction($productID, $quantity)
		{
			
			$deletOld  = RecipeContains::where('recipe_id', $productID)->get();
			$recipeStock = []; 
			foreach ($deletOld as $key => $value) {
					
				
				$updateStock = ProductInfo::find($value->product_info_stock_id);

				   $updateStock->current_quanitity =  $updateStock->current_quanitity - (unitConversion($value->unit_id, $value->quantity) * $quantity );
				   $updateStock->save();
				
				   
				//    below code is for debugging purpose, getting output of array

				// $recipeStock[] = [
				// 	// 'emp_id' =>  $getCurrentQuantity->current_quanitity,
				// 	'emp_2' =>  $updateStock->current_quanitity,				
				// ];
			}
			
			// return $recipeStock;
		}    