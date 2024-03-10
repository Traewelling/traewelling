<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void {
        $permission = Permission::create(['name' => 'view activity']);

        $roleAdmin = Role::findByName('admin');
        $roleAdmin->givePermissionTo($permission);
    }

    public function down(): void {
        $permission = Permission::findByName('view activity');

        $roleAdmin = Role::findByName('admin');
        $roleAdmin->revokePermissionTo($permission);

        $permission->delete();
    }
};
