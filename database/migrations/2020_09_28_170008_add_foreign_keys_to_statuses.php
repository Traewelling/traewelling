<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @todo Squash to statuses creation after resorting tables
 * Class AddForeignKeysToStatuses
 */
class AddForeignKeysToStatuses extends Migration
{

    public function up(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
    }
}
