<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oauth_client_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->text('url');
            $table->text('secret')->nullable();
            $table->unsignedInteger('events');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('webhooks');
    }
};
