<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;
// use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        /*------------Default Role-----------------------------------*/
        $role1 = Role::create([
            'id' => '1',
            'name' => 'Admin',
            'guard_name' => 'api'
        ]);
        $role2 = Role::create([
            'id' => '2',
            'name' => 'Cafe',
            'guard_name' => 'api'
        ]);
        $role3 = Role::create([
            'id' => '3',
            'name' => 'Employee',
            'guard_name' => 'api'
        ]);
        $role4 = Role::create([
            'id' => '4',
            'name' => 'Customer',
            'guard_name' => 'api'
        ]);
        $role5 = Role::create([
            'id' => '5',
            'name' => 'AdminEmployee',
            'guard_name' => 'api'
        ]);

        /*-----------Create Admin-------------*/
        $adminUser = new User();
        $adminUser->id                      = '1';
        $adminUser->role_id                 = '1';
        $adminUser->name                    = 'Admin';
        $adminUser->email                   = 'admin@gmail.com';
        $adminUser->password                = \Hash::make(12345678);
        $adminUser->save();

        $adminRole = Role::where('id','1')->first();
        $adminUser->assignRole($adminRole);

        /*-----------Create Cafe-------------*/
        $storeUser = new User();
        $storeUser->id                      = '2';
        $storeUser->role_id                 = '2';
        $storeUser->name                    = 'Cafe';
        $storeUser->email                   = 'cafe@gmail.com';
        $storeUser->password                = \Hash::make(12345678);
        $storeUser->save();

        $storeRole = Role::where('id','2')->first();
        $storeUser->assignRole($storeRole);

        /*-----------Create Employee-------------*/
        $employeeUser = new User();
        $employeeUser->id                      = '3';
        $employeeUser->role_id                 = '3';
        $employeeUser->name                    = 'Employee';
        $employeeUser->email                   = 'employee@gmail.com';
        $employeeUser->password                = \Hash::make(12345678);
        $employeeUser->save();

        $employeeRole = Role::where('id','3')->first();
        $employeeUser->assignRole($employeeRole);

        /*-----------Create Customer-------------*/
        $customerUser = new User();
        $customerUser->id                      = '4';
        $customerUser->role_id                 = '4';
        $customerUser->name                    = 'Customer';
        $customerUser->email                   = 'customer@gmail.com';
        $customerUser->password                = \Hash::make(12345678);
        $customerUser->save();

        $customerRole = Role::where('id','4')->first();
        $customerUser->assignRole($customerRole);
    }
}
