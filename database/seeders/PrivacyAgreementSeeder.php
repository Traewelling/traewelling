<?php

namespace Database\Seeders;

use App\Models\PrivacyAgreement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PrivacyAgreementSeeder extends Seeder
{

    public function run(): void {
        PrivacyAgreement::create([
                                     'body_md_de' => '# Allgemeiner Hinweis und Pflicht&shy;informationen',
                                     'body_md_en' => '# General notes and mandatory information',
                                     'valid_at'   => Carbon::now()->toIso8601String()
                                 ]);
    }
}
