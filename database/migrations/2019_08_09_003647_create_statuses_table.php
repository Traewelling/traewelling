<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{

    public function up(): void {
        Schema::create('statuses', function(Blueprint $table) {
            $table->id();
            $table->text('body')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('business')->default(0);
            $table->unsignedTinyInteger('visibility')->default(0);
            $table->string('type')->default('hafas');
            $table->unsignedBigInteger('event_id')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('statuses');
    }
}
