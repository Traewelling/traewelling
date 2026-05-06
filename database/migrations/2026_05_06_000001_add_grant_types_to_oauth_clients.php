<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', static function (Blueprint $table): void {
            $table->text('grant_types')->nullable()->after('revoked');
        });

        // Populate grant_types from the legacy personal_access_client / password_client flags.
        // Passport v13 uses this column to avoid dynamically including 'client_credentials'
        // for all confidential first-party clients (which broke the v13.7.1 security check).
        DB::table('oauth_clients')->lazyById()->each(static function (object $client): void {
            $grantTypes = match (true) {
                (bool) $client->personal_access_client => ['personal_access'],
                (bool) $client->password_client => ['password', 'refresh_token'],
                !empty($client->redirect) => ['authorization_code', 'refresh_token'],
                default => ['client_credentials'],
            };

            DB::table('oauth_clients')
                ->where('id', $client->id)
                ->update(['grant_types' => json_encode($grantTypes)]);
        });

        Schema::table('oauth_clients', static function (Blueprint $table): void {
            $table->text('grant_types')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', static function (Blueprint $table): void {
            $table->dropColumn('grant_types');
        });
    }
};
