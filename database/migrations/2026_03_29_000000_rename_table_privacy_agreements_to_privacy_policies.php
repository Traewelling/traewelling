<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('body_md_de');
            $table->text('body_md_en');
            $table->timestamp('valid_at');
            $table->timestamps();
        });

        DB::table('privacy_agreements')->orderBy('valid_at')->chunk(100, function ($agreements) {
            foreach ($agreements as $agreement) {
                DB::table('privacy_policies')->insert([
                    'id' => (string) Str::uuid(),
                    'body_md_de' => $agreement->body_md_de,
                    'body_md_en' => $agreement->body_md_en,
                    'valid_at' => $agreement->valid_at,
                    'created_at' => $agreement->created_at,
                    'updated_at' => $agreement->updated_at,
                ]);
            }
        });

        Schema::drop('privacy_agreements');
    }

    public function down(): void
    {
        Schema::create('privacy_agreements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('body_md_de');
            $table->text('body_md_en');
            $table->timestamp('valid_at');
            $table->timestamps();
        });

        DB::table('privacy_policies')->orderBy('valid_at')->chunk(1000, function ($agreements) {
            foreach ($agreements as $agreement) {
                DB::table('privacy_policies')->insert([
                    'body_md_de' => $agreement->body_md_de,
                    'body_md_en' => $agreement->body_md_en,
                    'valid_at' => $agreement->valid_at,
                    'created_at' => $agreement->created_at,
                    'updated_at' => $agreement->updated_at,
                ]);
            }
        });

        Schema::drop('privacy_policies');
    }
};
