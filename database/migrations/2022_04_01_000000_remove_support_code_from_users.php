<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::dropColumns('users', ['support_code']);
    }

    public function down(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->mediumInteger('support_code')->unsigned()->after('role')->nullable();
        });
    }
};
