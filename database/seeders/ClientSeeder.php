<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Database\Factories\ClientFactory;

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
        $clients = new ClientRepository();
        $clients->create($gertrud->id, 'Test Application', 'https://oauthdebugger.com/debug', null, false, false, false);
    }
}
