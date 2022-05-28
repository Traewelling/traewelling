<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUrlNullableAtEvents extends Migration
{
    public function up(): void {
        Schema::table('events', function(Blueprint $table) {
            $table->string('url')->nullable()->change();
            $table->string('host')->nullable()->change();

            //Part of this migration removed in v1.14 due to compatibility issues with SQLite testing.
            //Please upgrade to v1.13.2 first, run `php artisan migrate` and THEN you can upgrade to v1.14.
        });
    }

    public function down(): void {
        Schema::table('events', function(Blueprint $table) {
            $table->string('url')->change();
            $table->string('host')->change();
        });
    }
}
