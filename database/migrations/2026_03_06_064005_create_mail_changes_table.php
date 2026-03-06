<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('mail_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->string('old_email');
            $table->string('new_email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_changes');
    }
};
