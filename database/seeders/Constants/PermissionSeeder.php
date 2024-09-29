<?php

namespace Database\Seeders\Constants;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * This seeder is used to insert important constants into the database.
 * It is used to ensure that the constants are always present in the database.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void {
        //Create roles
        $roleAdmin                  = Role::updateOrCreate(['name' => 'admin']);
        $roleEventModerator         = Role::updateOrCreate(['name' => 'event-moderator']);
        $roleOpenBeta               = Role::updateOrCreate(['name' => 'open-beta']);
        $roleClosedBeta             = Role::updateOrCreate(['name' => 'closed-beta']);
        $roleDisallowManualTrips    = Role::updateOrCreate(['name' => 'disallow-manual-trips']);
        $roleDeactivateAccountUsage = Role::updateOrCreate(['name' => 'deactivate-account-usage']);

        //Create permissions
        $permissionViewBackend                    = Permission::updateOrCreate(['name' => 'view-backend']);
        $permissionViewEvents                     = Permission::updateOrCreate(['name' => 'view-events']);
        $permissionAcceptEvents                   = Permission::updateOrCreate(['name' => 'accept-events']);
        $permissionDenyEvents                     = Permission::updateOrCreate(['name' => 'deny-events']);
        $permissionCreateEvents                   = Permission::updateOrCreate(['name' => 'create-events']);
        $permissionUpdateEvents                   = Permission::updateOrCreate(['name' => 'update-events']);
        $permissionDeleteEvents                   = Permission::updateOrCreate(['name' => 'delete-events']);
        $permissionCreateManualTrip               = Permission::updateOrCreate(['name' => 'create-manual-trip']);
        $permissionViewActivity                   = Permission::updateOrCreate(['name' => 'view activity']);
        $permissionViewEventHistory               = Permission::updateOrCreate(['name' => 'view event history']);
        $permissionCreateStations                 = Permission::updateOrCreate(['name' => 'create stations']);
        $permissionUpdateStations                 = Permission::updateOrCreate(['name' => 'update stations']);
        $permissionDeleteStations                 = Permission::updateOrCreate(['name' => 'delete stations']);
        $permissionDisallowManualTrips            = Permission::updateOrCreate(['name' => 'disallow-manual-trips']);
        $permissionDisallowStatusCreation         = Permission::updateOrCreate(['name' => 'disallow-status-creation']);
        $permissionDisallowStatusVisibilityChange = Permission::updateOrCreate(['name' => 'disallow-status-visibility-change']);
        $permissionDisallowSocialInteraction      = Permission::updateOrCreate(['name' => 'disallow-social-interaction']);

        //Assign permissions to admin role
        $roleAdmin->givePermissionTo($permissionViewBackend);
        $roleAdmin->givePermissionTo($permissionViewEvents);
        $roleAdmin->givePermissionTo($permissionAcceptEvents);
        $roleAdmin->givePermissionTo($permissionDenyEvents);
        $roleAdmin->givePermissionTo($permissionCreateEvents);
        $roleAdmin->givePermissionTo($permissionUpdateEvents);
        $roleAdmin->givePermissionTo($permissionDeleteEvents);
        $roleAdmin->givePermissionTo($permissionCreateManualTrip);
        $roleAdmin->givePermissionTo($permissionViewActivity);
        $roleAdmin->givePermissionTo($permissionViewEventHistory);
        $roleAdmin->givePermissionTo($permissionCreateStations);
        $roleAdmin->givePermissionTo($permissionUpdateStations);
        $roleAdmin->givePermissionTo($permissionDeleteStations);

        //Assign permissions to disallow-manual-trips role
        $roleDisallowManualTrips->givePermissionTo($permissionDisallowManualTrips);

        //Assign permissions to deactivate-account-usage role
        $roleDeactivateAccountUsage->givePermissionTo($permissionDisallowManualTrips);
        $roleDeactivateAccountUsage->givePermissionTo($permissionDisallowStatusCreation);
        $roleDeactivateAccountUsage->givePermissionTo($permissionDisallowStatusVisibilityChange);
        $roleDeactivateAccountUsage->givePermissionTo($permissionDisallowSocialInteraction);

        //Assign permissions to event-moderator role
        $roleEventModerator->givePermissionTo($permissionViewBackend);
        $roleEventModerator->givePermissionTo($permissionViewEvents);
        $roleEventModerator->givePermissionTo($permissionAcceptEvents);
        $roleEventModerator->givePermissionTo($permissionDenyEvents);
        $roleEventModerator->givePermissionTo($permissionUpdateEvents);

        //Revoke permissions from closed-beta role
        $roleClosedBeta->revokePermissionTo($permissionCreateManualTrip); //now in open-beta

        //Assign permissions to open-beta role
        $roleOpenBeta->givePermissionTo($permissionCreateManualTrip);
    }
}
