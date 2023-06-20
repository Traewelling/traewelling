<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('events', static function (Blueprint $table) {
            $table->dateTime('event_start')->nullable()->after('end');
            $table->dateTime('event_end')->nullable()->after('event_start');
            $table->unsignedBigInteger('approved_by')->nullable()->after('event_end');
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }


    public function down(): void
    {
        Schema::table('events', static function (Blueprint $table) {
            $table->dropForeign('events_approved_by_foreign');
            $table->dropColumn(['event_start', 'event_end', 'approved_by']);
        });
    }
};
