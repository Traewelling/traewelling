<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventSuggestionsTable extends Migration
{
    public function up(): void {
        Schema::create('event_suggestions', function(Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('name');
            $table->string('host')->nullable();
            $table->string('url');
            $table->unsignedBigInteger('train_station_id')->nullable();
            $table->timestamp('begin')->nullable()->default(null);
            $table->timestamp('end')->nullable()->default(null);

            $table->boolean('processed')->default(false);

            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
            $table->foreign('train_station_id')
                  ->references('id')
                  ->on('train_stations');
        });
    }

    public function down(): void {
        Schema::dropIfExists('event_suggestions');
    }
}
