<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeColumnForTrainCheckins extends Migration
{

    public function up(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->unsignedInteger('distance')
                  ->comment('meter')
                  ->nullable()
                  ->default(null)
                  ->change();
        });

        DB::table('train_checkins')->update([
                                                'distance' => DB::raw('distance * 1000'),
                                            ]);
    }

    public function down(): void {
        Schema::table('train_checkins', function(Blueprint $table) {
            $table->integer('distance')
                  ->comment('kilometers')
                  ->nullable()
                  ->default(null)
                  ->change();
        });

        DB::table('train_checkins')->update([
                                                'distance' => DB::raw('distance / 1000'),
                                            ]);
    }
}
