<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeStatusesToAcceptBusinessCheckinAsInteger extends Migration
{
    public function up() {
        Schema::table('statuses', function(Blueprint $table) {
            $table->integer('business')
                  ->default(0)
                  ->change();
        });

        DB::table('statuses')
            ->whereNull('business')
            ->update(['business' => '0']);
        DB::table('statuses')
            ->where('business', 'true')
            ->update(['business' => '1']);
        DB::table('statuses')
            ->where('business', 'false')
            ->update(['business' => '0']);
    }

    public function down() {
        Schema::table('statuses', function(Blueprint $table) {
            $table->boolean('business')
                ->change();
        });

        DB::table('statuses')
            ->where('business', '>=', '1')
            ->update(['business' => true]);
        DB::table('statuses')
            ->where('business', '0')
            ->update(['business' => false]);
    }
}
