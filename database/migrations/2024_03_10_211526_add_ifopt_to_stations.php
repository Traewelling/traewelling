<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('train_stations', static function(Blueprint $table) {
            $table->unsignedInteger('ifopt_e')->nullable()->comment('Stop Place Component / unused')->after('wikidata_id');
            $table->unsignedInteger('ifopt_d')->nullable()->comment('Stop place component')->after('wikidata_id');
            $table->unsignedInteger('ifopt_c')->nullable()->comment('Mode / Stop Place')->after('wikidata_id');
            $table->unsignedInteger('ifopt_b')->nullable()->comment('Administrative Area')->after('wikidata_id');
            $table->string('ifopt_a')->nullable()->comment('Country')->after('wikidata_id');

            $table->index(['ifopt_a', 'ifopt_b', 'ifopt_c', 'ifopt_d', 'ifopt_e'], 'ifopt');
        });
    }

    public function down(): void {
        Schema::table('train_stations', static function(Blueprint $table) {
            $table->dropIndex('ifopt');
            $table->dropColumn('ifopt_e');
            $table->dropColumn('ifopt_d');
            $table->dropColumn('ifopt_c');
            $table->dropColumn('ifopt_b');
            $table->dropColumn('ifopt_a');
        });
    }
};
