<?php

use App\Models\PrivacyPolicy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_policy_acceptances', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreignIdFor(PrivacyPolicy::class)->constrained();
            $table->timestamp('accepted_at');
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'privacy_policy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_policy_acceptances');
    }
};
