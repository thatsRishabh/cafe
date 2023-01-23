<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        app()['cache']->forget('spatie.permission.cache');
        // create roles and assign existing permissions

        // 1 for only admin
    	Permission::create(['name' => 'cafe-read', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-read','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-add', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-create','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-edit', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-edit','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-delete', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-delete','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-browse', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-browse','belongs_to'=>'1']);

        // 4 is for common of adimn and cafe 
        Permission::create(['name' => 'employee-read', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-read','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-add', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-create','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-edit', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-edit','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-delete', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-delete','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-browse', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-browse','belongs_to'=>'4']);

         Permission::create(['name' => 'dashboard-read', 'guard_name' => 'api','group_name'=>'dashboard','se_name'=>'dashboard-read','belongs_to'=>'4']);
         Permission::create(['name' => 'dashboard-add', 'guard_name' => 'api','group_name'=>'dashboard','se_name'=>'dashboard-create','belongs_to'=>'4']);
         Permission::create(['name' => 'dashboard-edit', 'guard_name' => 'api','group_name'=>'dashboard','se_name'=>'dashboard-edit','belongs_to'=>'4']);
         Permission::create(['name' => 'dashboard-delete', 'guard_name' => 'api','group_name'=>'dashboard','se_name'=>'dashboard-delete','belongs_to'=>'4']);
         Permission::create(['name' => 'dashboard-browse', 'guard_name' => 'api','group_name'=>'dashboard','se_name'=>'dashboard-browse','belongs_to'=>'4']);

        // 10 is for common for all, admin, cafe, employee, adminEmployee
        Permission::create(['name' => 'customer-read', 'guard_name' => 'api','group_name'=>'customer','se_name'=>'customer-read','belongs_to'=>'10']);
        Permission::create(['name' => 'customer-add', 'guard_name' => 'api','group_name'=>'customer','se_name'=>'customer-create','belongs_to'=>'10']);
        Permission::create(['name' => 'customer-edit', 'guard_name' => 'api','group_name'=>'customer','se_name'=>'customer-edit','belongs_to'=>'10']);
        Permission::create(['name' => 'customer-delete', 'guard_name' => 'api','group_name'=>'customer','se_name'=>'customer-delete','belongs_to'=>'10']);
        Permission::create(['name' => 'customer-browse', 'guard_name' => 'api','group_name'=>'customer','se_name'=>'customer-browse','belongs_to'=>'10']);

         // 2 is for cafe
         Permission::create(['name' => 'productInfo-read', 'guard_name' => 'api','group_name'=>'productInfo','se_name'=>'productInfo-read','belongs_to'=>'2']);
         Permission::create(['name' => 'productInfo-add', 'guard_name' => 'api','group_name'=>'productInfo','se_name'=>'productInfo-create','belongs_to'=>'2']);
         Permission::create(['name' => 'productInfo-edit', 'guard_name' => 'api','group_name'=>'productInfo','se_name'=>'productInfo-edit','belongs_to'=>'2']);
         Permission::create(['name' => 'productInfo-delete', 'guard_name' => 'api','group_name'=>'productInfo','se_name'=>'productInfo-delete','belongs_to'=>'2']);
         Permission::create(['name' => 'productInfo-browse', 'guard_name' => 'api','group_name'=>'productInfo','se_name'=>'productInfo-browse','belongs_to'=>'2']);

         Permission::create(['name' => 'recipe-read', 'guard_name' => 'api','group_name'=>'recipe','se_name'=>'recipe-read','belongs_to'=>'2']);
         Permission::create(['name' => 'recipe-add', 'guard_name' => 'api','group_name'=>'recipe','se_name'=>'recipe-create','belongs_to'=>'2']);
         Permission::create(['name' => 'recipe-edit', 'guard_name' => 'api','group_name'=>'recipe','se_name'=>'recipe-edit','belongs_to'=>'2']);
         Permission::create(['name' => 'recipe-delete', 'guard_name' => 'api','group_name'=>'recipe','se_name'=>'recipe-delete','belongs_to'=>'2']);
         Permission::create(['name' => 'recipe-browse', 'guard_name' => 'api','group_name'=>'recipe','se_name'=>'recipe-browse','belongs_to'=>'2']);

         Permission::create(['name' => 'expense-read', 'guard_name' => 'api','group_name'=>'expense','se_name'=>'expense-read','belongs_to'=>'2']);
         Permission::create(['name' => 'expense-add', 'guard_name' => 'api','group_name'=>'expense','se_name'=>'expense-create','belongs_to'=>'2']);
         Permission::create(['name' => 'expense-edit', 'guard_name' => 'api','group_name'=>'expense','se_name'=>'expense-edit','belongs_to'=>'2']);
         Permission::create(['name' => 'expense-delete', 'guard_name' => 'api','group_name'=>'expense','se_name'=>'expense-delete','belongs_to'=>'2']);
         Permission::create(['name' => 'expense-browse', 'guard_name' => 'api','group_name'=>'expense','se_name'=>'expense-browse','belongs_to'=>'2']);

         Permission::create(['name' => 'employeeAttendence-read', 'guard_name' => 'api','group_name'=>'employeeAttendence','se_name'=>'employeeAttendence-read','belongs_to'=>'2']);
         Permission::create(['name' => 'employeeAttendence-add', 'guard_name' => 'api','group_name'=>'employeeAttendence','se_name'=>'employeeAttendence-create','belongs_to'=>'2']);
         Permission::create(['name' => 'employeeAttendence-edit', 'guard_name' => 'api','group_name'=>'employeeAttendence','se_name'=>'employeeAttendence-edit','belongs_to'=>'2']);
         Permission::create(['name' => 'employeeAttendence-delete', 'guard_name' => 'api','group_name'=>'employeeAttendence','se_name'=>'employeeAttendence-delete','belongs_to'=>'2']);
         Permission::create(['name' => 'employeeAttendence-browse', 'guard_name' => 'api','group_name'=>'employeeAttendence','se_name'=>'employeeAttendence-browse','belongs_to'=>'2']);

         Permission::create(['name' => 'customerAccount-read', 'guard_name' => 'api','group_name'=>'customerAccount','se_name'=>'customerAccount-read','belongs_to'=>'2']);
         Permission::create(['name' => 'customerAccount-add', 'guard_name' => 'api','group_name'=>'customerAccount','se_name'=>'customerAccount-create','belongs_to'=>'2']);
         Permission::create(['name' => 'customerAccount-edit', 'guard_name' => 'api','group_name'=>'customerAccount','se_name'=>'customerAccount-edit','belongs_to'=>'2']);
         Permission::create(['name' => 'customerAccount-delete', 'guard_name' => 'api','group_name'=>'customerAccount','se_name'=>'customerAccount-delete','belongs_to'=>'2']);
         Permission::create(['name' => 'customerAccount-browse', 'guard_name' => 'api','group_name'=>'customerAccount','se_name'=>'customerAccount-browse','belongs_to'=>'2']);

         Permission::create(['name' => 'empoyeeSalary-read', 'guard_name' => 'api','group_name'=>'empoyeeSalary','se_name'=>'employeeSalary-read','belongs_to'=>'2']);
         Permission::create(['name' => 'empoyeeSalary-add', 'guard_name' => 'api','group_name'=>'empoyeeSalary','se_name'=>'employeeSalary-create','belongs_to'=>'2']);
         Permission::create(['name' => 'empoyeeSalary-edit', 'guard_name' => 'api','group_name'=>'empoyeeSalary','se_name'=>'employeeSalary-edit','belongs_to'=>'2']);
         Permission::create(['name' => 'empoyeeSalary-delete', 'guard_name' => 'api','group_name'=>'empoyeeSalary','se_name'=>'employeeSalary-delete','belongs_to'=>'2']);
         Permission::create(['name' => 'empoyeeSalary-browse', 'guard_name' => 'api','group_name'=>'empoyeeSalary','se_name'=>'employeeSalary-browse','belongs_to'=>'2']);

         Permission::create(['name' => 'productStockManage-read', 'guard_name' => 'api','group_name'=>'productStockManage','se_name'=>'productStockManage-read','belongs_to'=>'2']);
         Permission::create(['name' => 'productStockManage-add', 'guard_name' => 'api','group_name'=>'productStockManage','se_name'=>'productStockManage-create','belongs_to'=>'2']);
         Permission::create(['name' => 'productStockManage-edit', 'guard_name' => 'api','group_name'=>'productStockManage','se_name'=>'productStockManage-edit','belongs_to'=>'2']);
         Permission::create(['name' => 'productStockManage-delete', 'guard_name' => 'api','group_name'=>'productStockManage','se_name'=>'productStockManage-delete','belongs_to'=>'2']);
         Permission::create(['name' => 'productStockManage-browse', 'guard_name' => 'api','group_name'=>'productStockManage','se_name'=>'productStockManage-browse','belongs_to'=>'2']);

          // 5 is for common for cafe, employee,   
        Permission::create(['name' => 'unit-read', 'guard_name' => 'api','group_name'=>'unit','se_name'=>'unit-read','belongs_to'=>'5']);
        Permission::create(['name' => 'unit-add', 'guard_name' => 'api','group_name'=>'unit','se_name'=>'unit-create','belongs_to'=>'5']);
        Permission::create(['name' => 'unit-edit', 'guard_name' => 'api','group_name'=>'unit','se_name'=>'unit-edit','belongs_to'=>'5']);
        Permission::create(['name' => 'unit-delete', 'guard_name' => 'api','group_name'=>'unit','se_name'=>'unit-delete','belongs_to'=>'5']);
        Permission::create(['name' => 'unit-browse', 'guard_name' => 'api','group_name'=>'unit','se_name'=>'unit-browse','belongs_to'=>'5']);

        Permission::create(['name' => 'category-read', 'guard_name' => 'api','group_name'=>'category','se_name'=>'category-read','belongs_to'=>'5']);
        Permission::create(['name' => 'category-add', 'guard_name' => 'api','group_name'=>'category','se_name'=>'category-create','belongs_to'=>'5']);
        Permission::create(['name' => 'category-edit', 'guard_name' => 'api','group_name'=>'category','se_name'=>'category-edit','belongs_to'=>'5']);
        Permission::create(['name' => 'category-delete', 'guard_name' => 'api','group_name'=>'category','se_name'=>'category-delete','belongs_to'=>'5']);
        Permission::create(['name' => 'category-browse', 'guard_name' => 'api','group_name'=>'category','se_name'=>'category-browse','belongs_to'=>'5']);

        Permission::create(['name' => 'productMenu-read', 'guard_name' => 'api','group_name'=>'productMenu','se_name'=>'productMenu-read','belongs_to'=>'5']);
        Permission::create(['name' => 'productMenu-add', 'guard_name' => 'api','group_name'=>'productMenu','se_name'=>'productMenu-create','belongs_to'=>'5']);
        Permission::create(['name' => 'productMenu-edit', 'guard_name' => 'api','group_name'=>'productMenu','se_name'=>'productMenu-edit','belongs_to'=>'5']);
        Permission::create(['name' => 'productMenu-delete', 'guard_name' => 'api','group_name'=>'productMenu','se_name'=>'productMenu-delete','belongs_to'=>'5']);
        Permission::create(['name' => 'productMenu-browse', 'guard_name' => 'api','group_name'=>'productMenu','se_name'=>'productMenu-browse','belongs_to'=>'5']);

        Permission::create(['name' => 'order-read', 'guard_name' => 'api','group_name'=>'order','se_name'=>'order-read','belongs_to'=>'5']);
        Permission::create(['name' => 'order-add', 'guard_name' => 'api','group_name'=>'order','se_name'=>'order-create','belongs_to'=>'5']);
        Permission::create(['name' => 'order-edit', 'guard_name' => 'api','group_name'=>'order','se_name'=>'order-edit','belongs_to'=>'5']);
        Permission::create(['name' => 'order-delete', 'guard_name' => 'api','group_name'=>'order','se_name'=>'order-delete','belongs_to'=>'5']);
        Permission::create(['name' => 'order-browse', 'guard_name' => 'api','group_name'=>'order','se_name'=>'order-browse','belongs_to'=>'5']);
        
        // Permission::create(['name' => 'role-browse', 'guard_name' => 'api','group_name'=>'role','se_name'=>'role-browse','belongs_to'=>'3']);
    	// Permission::create(['name' => 'role-read', 'guard_name' => 'api','group_name'=>'role','se_name'=>'role-read','belongs_to'=>'3']);
    	// Permission::create(['name' => 'role-add', 'guard_name' => 'api','group_name'=>'role','se_name'=>'role-add','belongs_to'=>'3']);
    	// Permission::create(['name' => 'role-edit', 'guard_name' => 'api','group_name'=>'role','se_name'=>'role-edit','belongs_to'=>'3']);
    	// Permission::create(['name' => 'role-delete', 'guard_name' => 'api','group_name'=>'role','se_name'=>'role-delete','belongs_to'=>'3']);


    	// Permission::create(['name' => 'userType-browse', 'guard_name' => 'api','group_name'=>'userType','se_name'=>'userType-browse','belongs_to'=>'1']);
    	// Permission::create(['name' => 'userType-add', 'guard_name' => 'api','group_name'=>'userType','se_name'=>'userType-add','belongs_to'=>'1']);
    	// Permission::create(['name' => 'userType-read', 'guard_name' => 'api','group_name'=>'userType','se_name'=>'userType-read','belongs_to'=>'1']);
    	// Permission::create(['name' => 'userType-edit', 'guard_name' => 'api','group_name'=>'userType','se_name'=>'userType-edit','belongs_to'=>'1']);
    	// Permission::create(['name' => 'userType-delete', 'guard_name' => 'api','group_name'=>'userType','se_name'=>'userType-delete','belongs_to'=>'1']);


    	


/////////////////Changes End///////////////
        // $roles = Role::all();
        // foreach ($roles as $key => $role) {
        //     $permissions = Permission::where('belongs_to',$role->id)->get();
        //     $role->syncPermissions($permissions);
        // }

        // $adminRole = Role::where('id',1)->get();
        // $adminPermissions = Permission::whereIn('belongs_to',[1,3])->get();
        // $adminRole->syncPermissions($adminPermissions);

        // $storeRole = Role::where('id',2)->get();
        // $storePermissions = Permission::whereIn('belongs_to',[2,3])->get();
        // $storeRole->syncPermissions($storePermissions);
    
    }
}
