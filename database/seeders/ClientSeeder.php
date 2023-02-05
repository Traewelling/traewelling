<?php

namespace Database\Seeders;

use App\Models\User;
use App\Repositories\OAuthClientRepository;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gertrud = User::where('username', 'Gertrud123')->first();
        $clients = new OAuthClientRepository();
        $clients->create(
            $gertrud->id,
            'Test Application',
            'https://oauthdebugger.com/debug',
            null,
            false,
            false,
            false,
            "https://example.com/privacy",
            true,
            "https://example.com/webhook"
        );
    }
}
