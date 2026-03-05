<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('train_stations', function (Blueprint $table): void {
            foreach ([
                'train_stations_ibnr_unique',
                'train_stations_wikidata_id_foreign',
                'train_stations_wikidata_id_index',
                'train_stations_rilidentifier_index',
                'ifopt',
            ] as $index) {
                if (Schema::hasIndex('train_stations', $index)) {
                    $table->dropIndex($index);
                }
            }
            $table->dropColumn([
                'ibnr',
                'wikidata_id',
                'ifopt_a',
                'ifopt_b',
                'ifopt_c',
                'ifopt_d',
                'ifopt_e',
                'rilIdentifier',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('train_stations', function (Blueprint $table): void {
            $table->unsignedBigInteger('ibnr')->nullable()->unique();
            $table->string('wikidata_id')->nullable()->index();
            $table->string('ifopt_a')->nullable()->comment('Country');
            $table->unsignedInteger('ifopt_b')->nullable()->comment('Administrative Area');
            $table->unsignedInteger('ifopt_c')->nullable()->comment('Mode or Stop Place');
            $table->unsignedInteger('ifopt_d')->nullable()->comment('Stop Place or Stop Place Component');
            $table->unsignedInteger('ifopt_e')->nullable()->comment('Stop Place Component (or unused)');
            $table->string('rilIdentifier')->nullable()->index();
            $table->index(['ifopt_a', 'ifopt_b', 'ifopt_c', 'ifopt_d', 'ifopt_e'], 'ifopt');
        });
    }
};
