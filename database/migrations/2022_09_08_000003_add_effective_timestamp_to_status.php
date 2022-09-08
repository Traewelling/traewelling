<?php

use App\Models\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->timestampTz('effective_at')
                  ->nullable()
                  ->comment('Used for sorting statuses in the feed.')
                  ->after('event_id');
        });

        Status::where(DB::raw('1'), DB::raw('1'))
              ->update([
                           'effective_at' => DB::raw('created_at'),
                       ]);
    }

    public function down(): void {
        Schema::table('statuses', static function(Blueprint $table) {
            $table->dropColumn('effective_at');
        });
    }
};
