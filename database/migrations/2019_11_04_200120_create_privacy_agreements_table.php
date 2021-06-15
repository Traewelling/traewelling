<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivacyAgreementsTable extends Migration
{

    public function up(): void {
        Schema::create('privacy_agreements', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('body_md_de');
            $table->text('body_md_en');
            $table->timestamp('valid_at');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('privacy_agreements');
    }
}
