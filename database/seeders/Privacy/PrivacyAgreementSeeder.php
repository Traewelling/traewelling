<?php

namespace Database\Seeders\Privacy;

use App\Models\PrivacyAgreement;
use Illuminate\Database\Seeder;

class PrivacyAgreementSeeder extends Seeder
{

    public function run(): void {
        PrivacyAgreement::create([
                                     'body_md_de' => file_get_contents(__DIR__ . "/2019_11_04_privacy_de.md"),
                                     'body_md_en' => file_get_contents(__DIR__ . "/2019_11_04_privacy_en.md"),
                                     'valid_at'   => '2019-11-04 20:07:00'
                                 ]);
    }
}
