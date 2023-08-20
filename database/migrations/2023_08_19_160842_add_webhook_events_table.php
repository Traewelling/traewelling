<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('webhook_events', static function (Blueprint $table) {
            $table->foreignId('webhook_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->char('event', 32);
            $table->primary(['webhook_id', 'event']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('webhook_events');
    }
};
