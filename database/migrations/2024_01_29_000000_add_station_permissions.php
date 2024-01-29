<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void {
        $permissionCreate = Permission::create(['name' => 'create stations']);
        $permissionUpdate = Permission::create(['name' => 'update stations']);
        $permissionDelete = Permission::create(['name' => 'delete stations']);

        $roleAdmin = Role::findByName('admin');
        $roleAdmin->givePermissionTo($permissionCreate);
        $roleAdmin->givePermissionTo($permissionUpdate);
        $roleAdmin->givePermissionTo($permissionDelete);
    }

    public function down(): void {
        $permissionCreate = Permission::findByName('create stations');
        $permissionUpdate = Permission::findByName('update stations');
        $permissionDelete = Permission::findByName('delete stations');

        $roleAdmin = Role::findByName('admin');
        $roleAdmin->revokePermissionTo($permissionCreate);
        $roleAdmin->revokePermissionTo($permissionUpdate);
        $roleAdmin->revokePermissionTo($permissionDelete);

        $permissionCreate->delete();
        $permissionUpdate->delete();
        $permissionDelete->delete();
    }
};
