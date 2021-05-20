<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMutesTable extends Migration
{

    public function up(): void {
        Schema::create('user_mutes', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('muted_id');
            $table->timestamps();

            $table->unique(['user_id', 'muted_id']);

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
            $table->foreign('muted_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_mutes');
    }
}
