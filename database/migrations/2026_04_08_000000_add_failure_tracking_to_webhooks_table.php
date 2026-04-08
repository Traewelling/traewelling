<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('webhooks', function (Blueprint $table): void {
            $table->tinyInteger('consecutive_failures')->default(0)->after('secret');
            $table->timestamp('disabled_at')->nullable()->after('consecutive_failures');
        });
    }

    public function down(): void
    {
        Schema::table('webhooks', function (Blueprint $table): void {
            $table->dropColumn(['consecutive_failures', 'disabled_at']);
        });
    }
};
