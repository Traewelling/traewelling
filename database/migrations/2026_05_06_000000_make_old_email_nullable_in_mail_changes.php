<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_changes', function (Blueprint $table) {
            $table->string('old_email')->nullable()->change();
        });
    }
};
