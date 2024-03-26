<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('hafas_trips', static function(Blueprint $table) {
            $table->unsignedBigInteger('origin_id')->nullable(false)->change();
            $table->unsignedBigInteger('destination_id')->nullable(false)->change();
        });
    }
};
