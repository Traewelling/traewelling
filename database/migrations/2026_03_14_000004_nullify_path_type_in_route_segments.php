<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Resets path_type to NULL on all route_segments in a single statement.
// The existing path_type values are not meaningful in their current form and are cleared
// before deduplication so they do not act as a grouping dimension.
// A proper categorisation (e.g. rail, street, water) may be introduced separately.
// At ~116k rows this completes in well under a second, so batching is unnecessary.
return new class() extends Migration
{
    public function up(): void
    {
        DB::table('route_segments')->whereNotNull('path_type')->update(['path_type' => null]);
    }
};
