<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->string('moderation_notes')->nullable()
                  ->after('client_id')
                  ->comment('Notes from the moderation team - visible to the user');

            $table->boolean('lock_visibility')
                  ->default(false)
                  ->after('moderation_notes')
                  ->comment('Prevent the user from changing the visibility of the status?');

            $table->boolean('hide_body')
                  ->default(false)
                  ->after('lock_visibility')
                  ->comment('Hide the body of the status from other users?');
        });
    }

    public function down(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropColumn(['moderation_notes', 'lock_visibility', 'hide_body']);
        });
    }
};
