<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::dropIfExists('locations');
    }

    public function down(): void {
        Schema::create('locations', static function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address_street');
            $table->string('address_zip');
            $table->string('address_city');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
