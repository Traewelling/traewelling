<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('carriage_sequences', static function(Blueprint $table) {
            $table->unsignedInteger('order_number')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('carriage_sequences', static function(Blueprint $table) {
            $table->unsignedInteger('order_number')->change();
        });
    }
};
