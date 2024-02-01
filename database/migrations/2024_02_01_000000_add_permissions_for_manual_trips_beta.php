<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{

    public function up(): void {
        $permission = Permission::create(['name' => 'create-manual-trip']);

        Role::findByName('admin')->givePermissionTo($permission);
        Role::findByName('closed-beta')->givePermissionTo($permission);
    }

    public function down(): void {
        $permission = Permission::findByName('create-manual-trip');

        Role::findByName('admin')->revokePermissionTo($permission);
        Role::findByName('closed-beta')->revokePermissionTo($permission);

        $permission->delete();
    }
};
