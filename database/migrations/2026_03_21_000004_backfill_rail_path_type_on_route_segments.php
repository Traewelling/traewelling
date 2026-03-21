<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Sets path_type to 'rail' for all route_segments where it is currently NULL.
// All segments created before path_type was actively assigned are rail segments.
return new class() extends Migration
{
    public function up(): void
    {
        DB::table('route_segments')
            ->whereNull('path_type')
            ->update(['path_type' => 'rail']);
    }
};
