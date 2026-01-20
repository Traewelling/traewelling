<?php

use App\Models\RouteSegment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('train_stopovers', function (Blueprint $table) {
            $table->foreignIdFor(RouteSegment::class)->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('train_stopovers', function (Blueprint $table) {
            $table->dropForeign(['route_segment_id']);
            $table->dropColumn('route_segment_id');
        });
    }
};
