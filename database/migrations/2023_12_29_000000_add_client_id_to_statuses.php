<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('tweet_id');
            $table->foreign('client_id')->references('id')->on('oauth_clients')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('statuses', function(Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
