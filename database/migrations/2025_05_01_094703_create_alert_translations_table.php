<?php

use App\Models\Alert;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('alert_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Alert::class)->constrained('alerts')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('title');
            $table->string('content');
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_translations');
    }
};
