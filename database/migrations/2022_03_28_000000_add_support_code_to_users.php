<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    private static array $supportCodes = [];

    public static function up(): void {
        Schema::table('users', static function(Blueprint $table) {
            $table->mediumInteger('support_code')->unsigned()->after('role')->nullable();
        });

        foreach (User::all() as $user) {
            $user->update(['support_code' => self::getNewSupportCode()]);
        }
    }

    public static function down(): void {
        Schema::dropColumns('users', ['support_code']);
    }

    private static function getNewSupportCode(): int {
        $supportCode = random_int(100000, 999999);
        while (in_array($supportCode, self::$supportCodes, true)) {
            $supportCode = random_int(100000, 999999);
        }
        self::$supportCodes[] = $supportCode;
        return $supportCode;
    }
};
