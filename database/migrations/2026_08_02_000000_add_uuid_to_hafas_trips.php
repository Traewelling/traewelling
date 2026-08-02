<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE hafas_trips ADD COLUMN uuid CHAR(36) NULL, ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('hafas_trips', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('hafas_trips', function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
