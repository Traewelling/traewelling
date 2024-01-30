<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void {
        $permission = Permission::create(['name' => 'view event history']);
        Role::findByName('admin')->givePermissionTo($permission);
    }

    public function down(): void {
        Permission::findByName('view event history')->delete();
    }
};
