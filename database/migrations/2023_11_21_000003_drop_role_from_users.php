<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::dropColumns('users', ['role']);
    }

    public function down(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->boolean('role')->default(0)->after('privacy_hide_days');
        });
    }
};
