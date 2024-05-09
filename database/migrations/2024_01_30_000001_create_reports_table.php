<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('reports', static function(Blueprint $table) {
            $table->id();
            $table->string('status')->default('open')->comment('Enum ReportStatus');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('reason')->nullable()->comment('Enum ReportReason or null.');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('reporter_id')->nullable();
            $table->timestamps();

            $table->foreign('reporter_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('reports');
    }
};
