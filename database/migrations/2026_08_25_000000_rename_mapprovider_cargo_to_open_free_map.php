<?php

use App\Enum\MapProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->string('mapprovider')
                ->default(MapProvider::OPEN_FREE_MAP->value)
                ->change();
        });

        DB::table('users')->where('mapprovider', 'cargo')->update(['mapprovider' => MapProvider::OPEN_FREE_MAP->value]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('mapprovider', MapProvider::OPEN_FREE_MAP->value)
            ->update(['mapprovider' => 'cargo']);

        Schema::table('users', static function (Blueprint $table) {
            $table->enum('mapprovider', ['cargo', MapProvider::OPEN_RAILWAY_MAP->value])
                ->default('cargo')
                ->change();
        });
    }
};
