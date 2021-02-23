<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainCheckinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('train_checkins', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('status_id')
                  ->unique();
            $table->string('trip_id');
            $table->string('origin');
            $table->string('destination');
            $table->integer('distance');
            $table->timestampTz('departure')
                  ->comment('planned departure');
            $table->timestampTz('arrival')
                  ->nullable()
                  ->comment('planned arrival');
            $table->integer('points')->nullable();
            $table->integer('delay')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('train_checkins');
    }
}
