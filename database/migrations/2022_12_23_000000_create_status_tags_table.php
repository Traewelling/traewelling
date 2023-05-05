<?php

use App\Enum\StatusVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::create('status_tags', static function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id');
            $table->string('key');
            $table->string('value');
            $table->unsignedTinyInteger('visibility')->default(StatusVisibility::PRIVATE->value);
            $table->timestamps();

            $table->unique(['status_id', 'key']);
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('status_tags');
    }
};
