<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\StoreController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




Route::namespace('App\Http\Controllers\Api\Common')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        // Route::get('unauthorized', 'unauthorized')->name('unauthorized');
        // Route::post('login', 'login')->name('login');
        // Route::post('forgot-password', 'forgotPassword')->name('forgot-password');
        // Route::post('update-password', 'updatePassword')->name('update-password');
        // Route::post('logout', 'logout')->name('logout')->middleware('auth:api');
        // Route::post('change-password', 'changePassword')->name('changePassword')->middleware('auth:api');

        Route::post('user-login', 'login');
         Route::post('user-logout', 'logout')->middleware('auth:api');
         Route::post('change-password', 'changePassword')->middleware('auth:api');
    });

    // Route::post('user-login', [App\Http\Controllers\Api\Common\UserLoginController::class, 'login']);

    Route::group(['middleware' => 'auth:api'],function () {
    

        Route::controller(EmployeeController::class)->group(function () {
            Route::post('employees', 'searchEmployee');
            Route::post('employee-update/{id?}', 'update');
            Route::resource('employee', EmployeeController::class)->only([
                'store','destroy','show' ]);
        });

        Route::controller(CustomerController::class)->group(function () {
            Route::post('customers', 'searchCustomer');
            Route::resource('customer', CustomerController::class)->only([
                'store','destroy','show', 'update' ]);
        });

        Route::controller(CategoryController::class)->group(function () {
            Route::post('categorys', 'searchCategory');
            Route::post('subcategorys', 'searchSubcategory');
            Route::post('category-update/{id?}', 'update');
            Route::resource('category', CategoryController::class)->only([
                'store','destroy','show' ]);
        });

        Route::controller(ProductMenuController::class)->group(function () {
            Route::post('product-menus', 'searchProductMenu');
            Route::post('product-menus-list', 'productMenuList');
            Route::post('product-menu-update/{id?}', 'update');
            Route::resource('product-menu', ProductMenuController::class)->only([
                'store','destroy','show' ]);
        });
 // Unit
        Route::controller(UnitController::class)->group(function () {
            Route::post('units', 'searchUnit');
            Route::resource('unit', UnitController::class)->only([
                'store','destroy','show', 'update' ]);
        });
 // product-info
        Route::controller(ProductInfoController::class)->group(function () {
            Route::post('product-infos', 'searchProductInfo');
            Route::post('excel-import', 'excelImport');
            Route::resource('product-info', ProductInfoController::class)->only([
                'store','destroy','show', 'update' ]);
        });

 // ProductStockManage
        Route::controller(ProductStockManageController::class)->group(function () {
            Route::post('product-stock-manages', 'searchProductStockManage');
            // Route::post('customer-update/{id?}', 'update');
            Route::resource('product-stock-manage', ProductStockManageController::class)->only([
                'store','destroy','show', 'update' ]);
        });

   // recipe
            Route::controller(RecipeController::class)->group(function () {
                Route::post('recipes', 'searchRecipe');
                Route::resource('recipe', RecipeController::class)->only([
                    'store','destroy','show', 'update' ]);
            });
  // Order
            Route::controller(OrderController::class)->group(function () {
                Route::post('orders', 'searchOrder');
                Route::get('print-order/{id?}', 'printOrder'); 
                Route::resource('order', OrderController::class)->only([
                    'store','destroy','show', 'update' ]);
            });
    // expense
            Route::controller(ExpenseController::class)->group(function () {
                Route::post('expenses', 'searchExpense');
                Route::resource('expense', ExpenseController::class)->only([
                    'store','destroy','show', 'update' ]);
            });
            
        // SalaryManagement
        Route::controller(SalaryManagementController::class)->group(function () {
            Route::post('salary-managements', 'searchSalary');
            Route::resource('salary-management', SalaryManagementController::class)->only([
                'store','destroy','show', 'update' ]);
        });      
// EmployeeAttendence
        Route::controller(EmployeeAttendenceController::class)->group(function () {
            Route::post('employee-attendences', 'searchEmployeeAttendence');
            Route::resource('employee-attendence', EmployeeAttendenceController::class)->only([
                'store','destroy','show', 'update' ]);
            Route::get('employee-id', 'employeeID'); 
            Route::post('monthly-attendence', 'monthlyAttendence'); 
            Route::post('attendences-date-wise', 'dateWiseSearch'); 
        });

 // CustomerAccountManage
        Route::controller(CustomerAccountManageController::class)->group(function () {
            Route::post('customer-account-manages', 'searchCustomerAccount');
            Route::resource('customer-account-manage', CustomerAccountManageController::class)->only([
                'store','destroy','show', 'update' ]);
        });
        // Packaging
        Route::controller(PackagingController::class)->group(function () {
            Route::post('packagings', 'searchPackaging');
            Route::resource('packaging', PackagingController::class)->only([
                'store','destroy','show', 'update' ]);
        });
 // dashboard        
            Route::controller(DashboardController::class)->group(function () {
                Route::post('dashboard', 'dashboard');
                Route::post('category-wise-list', 'categoryWiseList'); 
                Route::post('dashboard-graph', 'dashboardGraph'); 
                Route::post('dashboard-graph-list', 'dashboardGraphByName'); 
            });
    });
});


Route::namespace('App\Http\Controllers\Api\Admin')->group(function () {
    Route::group(['middleware' => 'auth:api'],function () {
        Route::group(['middleware' => 'admin'],function () {
        
            Route::controller(CafeController::class)->group(function () {
                Route::post('cafes', 'searchCafe');
                Route::post('cafe-child', 'childLogin');
                Route::post('cafe-subscription', 'cafeSubscription');
                Route::put('cafe-subscription-update/{id?}', 'cafeSubscriptionUpdate');
                Route::post('cafe-update/{id?}', 'update');
                Route::resource('cafe', CafeController::class)->only([
                    'store','destroy','show' ]);
            });
        });
    });
});

Route::namespace('App\Http\Controllers\Api\Cafe')->group(function () {
    Route::group(['middleware' => 'auth:api'],function () {
        Route::group(['middleware' => 'cafe'],function () {

        
        });
    });
});

