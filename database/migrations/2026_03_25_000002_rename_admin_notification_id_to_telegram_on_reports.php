<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('reports', static function (Blueprint $table) {
            $table->renameColumn('admin_notification_id', 'telegram_notification_id');
        });
    }

    public function down(): void
    {
        Schema::table('reports', static function (Blueprint $table) {
            $table->renameColumn('telegram_notification_id', 'admin_notification_id');
        });
    }
};
