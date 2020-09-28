<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('avatar')->default('user.jpg');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('privacy_ack_at')->nullable();
            $table->string('password')->nullable();
            $table->float('train_distance')->default(0.00);
            $table->unsignedInteger('train_duration')->default(0); //travel_time
            $table->unsignedInteger('points')->default(0);
            $table->boolean('always_dbl')->default(false);
            $table->integer('home_id')->unsigned()
                ->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
