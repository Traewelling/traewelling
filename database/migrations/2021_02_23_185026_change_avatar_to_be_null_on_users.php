<?php1

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeAvatarToBeNullOnUsers extends Migration
{

    public function up(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->string('avatar')
                  ->nullable()
                  ->default(null)
                  ->change();
        });

        DB::table('users')
          ->where('avatar', 'user.jpg')
          ->update(['avatar' => null]);
    }


    public function down(): void {
        Schema::table('users', function(Blueprint $table) {
            $table->string('avatar')
                  ->default('user.jpg')
                  ->change();
        });

        DB::table('users')
          ->whereNull('avatar')
          ->update(['avatar' => 'user.jpg']);
    }
}
