<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->tinyInteger('time_offset')
                  ->nullable()
                  ->after('longitude')
                  ->comment('Defines the offset of the train station relative to Europe/Berlin');
            $table->boolean('shift_time')
                  ->nullable()
                  ->after('time_offset')
                  ->comment('If false, the timezone of the hafas request will not be shifted to Europe/Berlin');
        });
    }

    public function down(): void
    {
        Schema::table('train_stations', function (Blueprint $table) {
            $table->dropColumn('time_offset');
            $table->dropColumn('shift_time');
        });
    }
};
