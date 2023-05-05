<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('oauth_auth_codes', static function(Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->index(['user_id']);
            $table->unsignedBigInteger('client_id')->change();
        });
    }
};
