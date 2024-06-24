<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * We know, there is a function "renameColumn" in Laravel, but it's not supported by SQLite.
 * So this is our manual migration to rename the columns and don't break SQLite.
 */
return new class extends Migration
{
    public function up(): void {
        Schema::table('events', static function(Blueprint $table) {
            // add new columns
            $table->dateTime('checkin_start')->nullable()->after('begin');
            $table->dateTime('checkin_end')->nullable()->after('end');

            // add comment to existing columns
            $table->dateTime('event_start')->comment('If different from checkin_start')->nullable()->change();
            $table->dateTime('event_end')->comment('If different from checkin_end')->nullable()->change();
        });

        // update existing records
        DB::table('events')->update([
                                        'checkin_start' => DB::raw('begin'),
                                        'checkin_end'   => DB::raw('end'),
                                    ]);

        // drop old columns (if no sqlite)
        if (config('database.default') !== 'sqlite') {
            Schema::table('events', static function(Blueprint $table) {
                $table->dropColumn('begin');
                $table->dropColumn('end');
            });
        }

        // then make the new columns not nullable
        Schema::table('events', static function(Blueprint $table) {
            $table->dateTime('checkin_start')->nullable(false)->change();
            $table->dateTime('checkin_end')->nullable(false)->change();
        });
    }

    public function down(): void {
        Schema::table('events', static function(Blueprint $table) {
            // add old columns
            $table->dateTime('begin')->nullable()->after('checkin_start');
            $table->dateTime('end')->nullable()->after('checkin_end');
        });

        // update existing records
        DB::table('events')->update([
                                        'begin' => DB::raw('checkin_start'),
                                        'end'   => DB::raw('checkin_end'),
                                    ]);

        // drop new columns (if no sqlite)
        if (config('database.default') !== 'sqlite') {
            Schema::table('events', static function(Blueprint $table) {
                $table->dropColumn('checkin_start');
                $table->dropColumn('checkin_end');
            });
        }

        // then make the old columns not nullable
        Schema::table('events', static function(Blueprint $table) {
            $table->dateTime('begin')->nullable(false)->change();
            $table->dateTime('end')->nullable(false)->change();
        });
    }
};
