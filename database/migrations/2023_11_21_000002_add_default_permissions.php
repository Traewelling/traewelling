<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void {
        $roleAdmin          = Role::create(['name' => 'admin']);
        $roleEventModerator = Role::create(['name' => 'event-moderator']); //for accepting/denying events
        Role::create(['name' => 'open-beta']);                             //for experimental features that can be enabled by users
        Role::create(['name' => 'closed-beta']);                           //for experimental features that can be only enabled by admins for specific users

        $permissionViewBackend = Permission::create(['name' => 'view-backend']);

        $permissionViewEvents   = Permission::create(['name' => 'view-events']);
        $permissionAcceptEvents = Permission::create(['name' => 'accept-events']);
        $permissionDenyEvents   = Permission::create(['name' => 'deny-events']);
        $permissionCreateEvents = Permission::create(['name' => 'create-events']);
        $permissionUpdateEvents = Permission::create(['name' => 'update-events']);
        $permissionDeleteEvents = Permission::create(['name' => 'delete-events']);

        $roleAdmin->givePermissionTo($permissionViewBackend);
        $roleAdmin->givePermissionTo($permissionViewEvents);
        $roleAdmin->givePermissionTo($permissionAcceptEvents);
        $roleAdmin->givePermissionTo($permissionDenyEvents);
        $roleAdmin->givePermissionTo($permissionCreateEvents);
        $roleAdmin->givePermissionTo($permissionUpdateEvents);
        $roleAdmin->givePermissionTo($permissionDeleteEvents);

        $roleEventModerator->givePermissionTo($permissionViewBackend);
        $roleEventModerator->givePermissionTo($permissionViewEvents);
        $roleEventModerator->givePermissionTo($permissionAcceptEvents);
        $roleEventModerator->givePermissionTo($permissionDenyEvents);
        $roleEventModerator->givePermissionTo($permissionUpdateEvents);

        $oldAdmins = User::where('role', 10)->get();
        foreach ($oldAdmins as $oldAdmin) {
            $oldAdmin->assignRole('admin');
        }

        $experimentalUsers = User::where('experimental', true)->get();
        foreach ($experimentalUsers as $experimentalUser) {
            $experimentalUser->assignRole('open-beta');
        }
    }

    public function down(): void {
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->update(['role' => 10]);
        }

        Role::where('name', 'admin')->delete();
        Role::where('name', 'event-moderator')->delete();
        Role::where('name', 'open-beta')->delete();
        Role::where('name', 'closed-beta')->delete();
    }
};
