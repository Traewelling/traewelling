<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicy;
use Illuminate\Database\Seeder;

class PrivacyAgreementSeeder extends Seeder
{
    public function run(): void
    {
        PrivacyPolicy::factory()->create();
    }
}
