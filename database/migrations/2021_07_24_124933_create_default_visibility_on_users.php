<?php

use App\Enum\StatusVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultVisibilityOnUsers extends Migration
{

    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->unsignedTinyInteger('default_status_visibility')
                  ->default(StatusVisibility::PUBLIC)
                  ->after('private_profile');
        });
    }

    public function down(): void {
        Schema::dropColumns('users', ['default_status_visibility']);
    }
}
