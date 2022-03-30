<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public static function up(): void {
        Schema::table("users", function(Blueprint $table) {
            $table->tinyInteger('support_code', false, true)->after('role')->nullable();
        });

        foreach (User::all() as $user) {
            $user->update(['support_code' => random_int(100000, 999999)]);
            $user->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public static function down(): void {
        Schema::dropColumns('users', ['support_code']);
    }
};
