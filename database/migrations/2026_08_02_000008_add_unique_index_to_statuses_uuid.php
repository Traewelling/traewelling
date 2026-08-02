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
            DB::statement('ALTER TABLE statuses ADD UNIQUE INDEX statuses_uuid_unique (uuid), ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('statuses', function (Blueprint $table): void {
            $table->unique('uuid', 'statuses_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table): void {
            $table->dropUnique('statuses_uuid_unique');
        });
    }
};
