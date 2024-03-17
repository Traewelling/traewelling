<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "SQLite doesn't support multiple calls to dropColumn / renameColumn in a single modification."
 * -> so small steps here. (╯°□°)╯︵ ┻━┻
 */
return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            //Columns need to be nullable for the migration
            $table->unsignedBigInteger('origin_id')->nullable()->after('origin');
            $table->unsignedBigInteger('destination_id')->nullable()->after('destination');

            $table->foreign('origin_id')->references('id')->on('train_stations');
            $table->foreign('destination_id')->references('id')->on('train_stations');
        });

        DB::table('hafas_trips')->update([
                                             'origin_id'      => DB::raw('(SELECT id FROM train_stations WHERE ibnr = hafas_trips.origin)'),
                                             'destination_id' => DB::raw('(SELECT id FROM train_stations WHERE ibnr = hafas_trips.destination)'),
                                         ]);

        if (config('database.default') === 'sqlite') {
            //warn the user that they need to manually drop the columns
            echo PHP_EOL . PHP_EOL;
            echo "\033[41m"; //console background color red
            echo '--- WARNING ---' . PHP_EOL;
            echo 'SQLite doesn\'t support dropping foreign keys' . PHP_EOL;
            echo 'Please drop the columns "origin" and "destination" manually from the "hafas_trips" table.' . PHP_EOL;
            echo '--- WARNING ---';
            echo "\033[0m"; //reset console color
            echo PHP_EOL . PHP_EOL;
            return;
        }

        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropForeign(['origin']);
            $table->dropColumn('origin');
        });

        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropForeign(['destination']);
            $table->dropColumn('destination');
        });

        Schema::table('hafas_trips', static function(Blueprint $table) {
            //Then make the columns not nullable
            $table->unsignedBigInteger('origin_id')->nullable(false)->change();
            $table->unsignedBigInteger('destination_id')->nullable(false)->change();
        });
    }

    public function down(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->unsignedBigInteger('origin')->nullable()->after('origin_id');
            $table->unsignedBigInteger('destination')->nullable()->after('destination_id');

            $table->foreign('origin')->references('ibnr')->on('train_stations');
            $table->foreign('destination')->references('ibnr')->on('train_stations');
        });

        DB::table('hafas_trips')->update([
                                             'origin'      => DB::raw('(SELECT ibnr FROM train_stations WHERE id = hafas_trips.origin_id)'),
                                             'destination' => DB::raw('(SELECT ibnr FROM train_stations WHERE id = hafas_trips.destination_id)'),
                                         ]);

        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropForeign(['origin_id']);
            $table->dropColumn('origin_id');
        });

        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropColumn('destination_id');
        });

        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->unsignedBigInteger('origin')->nullable(false)->change();
            $table->unsignedBigInteger('destination')->nullable(false)->change();
        });
    }
};
