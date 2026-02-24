<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->date('checkin_start')->change();
            $table->date('checkin_end')->change();
            $table->date('event_start')->nullable()->change();
            $table->date('event_end')->nullable()->change();
        });

        Schema::table('event_suggestions', function (Blueprint $table): void {
            $table->date('begin')->nullable()->change();
            $table->date('end')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dateTime('checkin_start')->change();
            $table->dateTime('checkin_end')->change();
            $table->dateTime('event_start')->nullable()->change();
            $table->dateTime('event_end')->nullable()->change();
        });

        Schema::table('event_suggestions', function (Blueprint $table): void {
            $table->dateTime('begin')->nullable()->change();
            $table->dateTime('end')->nullable()->change();
        });
    }
};
