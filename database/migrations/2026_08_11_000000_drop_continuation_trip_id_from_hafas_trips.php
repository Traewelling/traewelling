<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// end experiment with interlined legs -> joined again (back to defaul tbehaviour)
return new class() extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE hafas_trips DROP FOREIGN KEY hafas_trips_continuation_trip_id_foreign');
            DB::statement('ALTER TABLE hafas_trips DROP COLUMN continuation_trip_id, ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('hafas_trips', function (Blueprint $table): void {
            $table->dropForeign(['continuation_trip_id']);
            $table->dropColumn('continuation_trip_id');
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table): void {
            $table->unsignedBigInteger('continuation_trip_id')->nullable();
            $table->foreign('continuation_trip_id')->references('id')->on('hafas_trips')->nullOnDelete();
        });
    }
};
