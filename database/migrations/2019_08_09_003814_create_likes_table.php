<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{

    public function up(): void {
        Schema::create('likes', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('status_id');
            $table->timestamps();

            $table->unique(['user_id', 'status_id']);

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
            $table->foreign('status_id')
                  ->references('id')
                  ->on('statuses')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('likes');
    }
}
