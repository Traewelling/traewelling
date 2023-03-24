<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserPrivateProfileKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("users", function(Blueprint $table) {
            $table->boolean('private_profile')->default(false)->after('home_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('private_profile');
        });
    }
}
