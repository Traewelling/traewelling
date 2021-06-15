<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMastodonServersTable extends Migration
{

    public function up(): void {
        Schema::create('mastodon_servers', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('domain')->unique();
            $table->string('client_id');
            $table->string('client_secret');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('mastodon_servers');
    }
}
