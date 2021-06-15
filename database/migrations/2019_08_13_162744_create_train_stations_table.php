<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainStationsTable extends Migration
{

    public function up(): void {
        Schema::create('train_stations', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ibnr')->unique();
            $table->string('rilIdentifier')->nullable()->default(null);
            $table->string('name');
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->timestamps();
        });

        //TODO: Squash to users when resorting tables
        Schema::table('users', function(Blueprint $table) {
            $table->foreign('home_id')
                  ->references('id')
                  ->on('train_stations')
                  ->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('train_stations');
    }
}
