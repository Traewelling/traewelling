<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibilityToStatuses extends Migration
{
    public function up(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->unsignedTinyInteger('visibility')
                  ->default(0)
                  ->after('business');
        });
    }

    public function down(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
}
