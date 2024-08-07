<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('train_stations', static function(Blueprint $table) {
            $table->dropForeign(['wikidata_id']);
        });
    }
};
