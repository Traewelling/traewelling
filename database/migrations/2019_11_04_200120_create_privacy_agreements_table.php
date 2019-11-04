<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrivacyAgreementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('privacy_agreements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('body_md_de');
            $table->text('body_md_en');
            $table->timestamp('valid_at');
            $table->timestamps();
        });

        // In dieser Form können später auch weitere Updates der Privacy Agreement einspielen.
        DB::table('privacy_agreements')->insert([
            'body_md_de' => file_get_contents(__DIR__ . "/2019_11_04_privacy_de.md"),
            'body_md_en' => file_get_contents(__DIR__ . "/2019_11_04_privacy_en.md"),
            'valid_at' => '2019-11-04 20:07:00'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('privacy_agreements');
    }
}
