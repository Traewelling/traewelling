<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->index(['type', 'identifier'], 'operator_identifiers_type_identifier');
            $table->index('source', 'operator_identifiers_source_identifier');
        });
    }

    public function down(): void
    {
        Schema::table('operator_identifiers', function (Blueprint $table) {
            $table->dropIndex('operator_identifiers_type_identifier');
            $table->dropIndex('operator_identifiers_source_identifier');
        });
    }
};
