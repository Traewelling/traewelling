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
            $table->unsignedBigInteger('trainstation')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('events', function(Blueprint $table) {
            $table->string('url')->change();
            $table->string('host')->change();
            $table->unsignedBigInteger('trainstation')->change();
        });
    }
}
