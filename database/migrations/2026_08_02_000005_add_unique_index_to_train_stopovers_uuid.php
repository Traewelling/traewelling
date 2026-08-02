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
            DB::statement('ALTER TABLE train_stopovers ADD UNIQUE INDEX train_stopovers_uuid_unique (uuid), ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('train_stopovers', function (Blueprint $table): void {
            $table->unique('uuid', 'train_stopovers_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::table('train_stopovers', function (Blueprint $table): void {
            $table->dropUnique('train_stopovers_uuid_unique');
        });
    }
};
