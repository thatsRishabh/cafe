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

    	Permission::create(['name' => 'storeSetting-read', 'guard_name' => 'api','se_name'=>'storeSetting-read','belongs_to'=>'1']);
    	Permission::create(['name' => 'storeSetting-add', 'guard_name' => 'api','se_name'=>'storeSetting-create','belongs_to'=>'1']);
    	Permission::create(['name' => 'storeSetting-edit', 'guard_name' => 'api','se_name'=>'storeSetting-edit','belongs_to'=>'1']);
    	Permission::create(['name' => 'storeSetting-delete', 'guard_name' => 'api','se_name'=>'storeSetting-delete','belongs_to'=>'1']);
    	Permission::create(['name' => 'storeSetting-browse', 'guard_name' => 'api','se_name'=>'storeSetting-browse','belongs_to'=>'1']);


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
