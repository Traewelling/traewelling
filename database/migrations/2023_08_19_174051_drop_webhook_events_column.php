<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('webhooks', static function (Blueprint $table) {
            $table->dropColumn('events');
        });
    }

    public function down(): void {
        Schema::table('webhooks', static function (Blueprint $table) {
            $table->addColumn('uint', 'events');
        });
    }
};
