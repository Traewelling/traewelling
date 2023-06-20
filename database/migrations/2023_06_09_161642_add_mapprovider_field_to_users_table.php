<?php

use App\Enum\MapProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->enum('mapprovider', [MapProvider::CARGO->value, MapProvider::OPEN_RAILWAY_MAP->value])
                  ->default(MapProvider::CARGO->value)
                  ->after('likes_enabled');
        });
    }

    public function down(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->dropColumn('mapprovider');
        });
    }
};
