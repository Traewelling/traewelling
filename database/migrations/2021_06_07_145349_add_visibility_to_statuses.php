<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibilityToStatuses extends Migration
{
    public function up() {
        Schema::table('statuses', function(Blueprint $table) {
            $table->tinyInteger('visibility')
                  ->default(0)
                  ->after('business');
        });
    }

    public function down() {
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
}
