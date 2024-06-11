<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        if (Schema::hasColumn('users', 'shadow_banned')) {
            Schema::table('users', static function(Blueprint $table) {
                $table->dropColumn('shadow_banned');
            });
        }
    }
};
