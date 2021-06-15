<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{

    public function up(): void {
        Schema::create('users', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('avatar')->nullable()->default(null);
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('privacy_ack_at')->nullable();
            $table->string('password')->nullable();
            $table->boolean('always_dbl')->default(false);
            $table->unsignedBigInteger('home_id')->nullable()->default(null);
            $table->tinyInteger('role')->default('0');
            $table->string('language', 12)->nullable()->default(null);
            $table->boolean('private_profile')->default(false);
            $table->boolean('prevent_index')
                  ->comment('prevent search engines from indexing this profile')
                  ->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
}
