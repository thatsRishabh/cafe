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

    	Permission::create(['name' => 'cafe-read', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-read','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-add', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-create','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-edit', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-edit','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-delete', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-delete','belongs_to'=>'1']);
    	Permission::create(['name' => 'cafe-browse', 'guard_name' => 'api','group_name'=>'cafe','se_name'=>'cafe-browse','belongs_to'=>'1']);

        // 4 is for common of adimn and cafe login
        Permission::create(['name' => 'employee-read', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-read','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-add', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-create','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-edit', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-edit','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-delete', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-delete','belongs_to'=>'4']);
    	Permission::create(['name' => 'employee-browse', 'guard_name' => 'api','group_name'=>'employee','se_name'=>'employee-browse','belongs_to'=>'4']);


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
