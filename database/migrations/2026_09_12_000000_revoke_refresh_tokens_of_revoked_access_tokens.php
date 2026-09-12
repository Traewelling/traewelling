<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('oauth_refresh_tokens')
            ->where('revoked', false)
            ->whereIn('access_token_id', function (Builder $query) {
                $query->select('id')->from('oauth_access_tokens')->where('revoked', true);
            })
            ->update(['revoked' => true]);
    }
};
