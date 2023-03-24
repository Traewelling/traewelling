<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('oauth_personal_access_clients', static function(Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('oauth_personal_access_clients');
    }
};
