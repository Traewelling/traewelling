<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        // set all trusted enum values to private, as after the revert there will be a 500 error instead
        DB::table('statuses')->where('visibility', '5')->update(['visibility' => '3']);
        DB::table('users')->where('default_status_visibility', '5')->update(['default_status_visibility' => '3']);
    }
};
