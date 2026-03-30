<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('privacy_agreements', function (Blueprint $table) {
            $table->rename('privacy_policies');
        });
    }

    public function down(): void
    {
        Schema::table('privacy_policies', function (Blueprint $table) {
            $table->rename('privacy_agreements');
        });
    }
};
