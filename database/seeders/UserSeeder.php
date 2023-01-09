<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;


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
            'name' => 'Employee',
            'guard_name' => 'api'
        ]);
        $role3 = Role::create([
            'id' => '3',
            'name' => 'Customer',
            'guard_name' => 'api'
        ]);

        /*-----------Create Admin-------------*/
        $adminUser = new User();
        $adminUser->id                      = '1';
        $adminUser->role_id                 = '1';
        $adminUser->name                    = 'admin';
        $adminUser->email                   = 'admin@gmail.com';
        $adminUser->password                = \Hash::make(12345678);
        $adminUser->save();

        // $adminRole = Role::where('id','1')->first();
        // $adminUser->assignRole($adminRole);

        /*-----------Create Employee-------------*/
        $adminUser = new User();
        $adminUser->id                      = '2';
        $adminUser->role_id                 = '2';
        $adminUser->name                    = 'Employee';
        $adminUser->email                   = 'employee@gmail.com';
        $adminUser->password                = \Hash::make(12345678);
        $adminUser->save();

        // $adminRole = Role::where('id','2')->first();
        // $adminUser->assignRole($adminRole);
    }
}
