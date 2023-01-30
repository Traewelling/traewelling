<?php

use App\Providers\AuthServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('oauth_access_tokens', function(Blueprint $table) {
            DB::table('oauth_access_tokens')->update(['scopes' => '["' . implode('","', array_keys(AuthServiceProvider::$scopes)) . '"]']);
        });
    }
};
