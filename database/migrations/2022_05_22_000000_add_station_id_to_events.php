<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        //Migration removed in v1.14 due to compatibility issues with SQLite testing.
        //Please upgrade to v1.13.2 first, run `php artisan migrate` and THEN you can upgrade to v1.14.
    }

    public function down(): void {
        Schema::table('events', static function(Blueprint $table) {
            $table->unsignedBigInteger('trainstation')->nullable()->after('station_id');
            $table->foreign('trainstation')->references('id')->on('train_stations');
        });

        DB::table('events')->update([
                                        'trainstation' => DB::raw('station_id'),
                                    ]);

        Schema::table('events', static function(Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropColumn(['station_id']);
        });
    }
};
