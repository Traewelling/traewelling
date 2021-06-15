<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogTable extends Migration
{

    public function up(): void {
        Schema::create('blogposts', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug');
            $table->string('author_name');
            $table->string('twitter_handle');
            $table->datetimeTz('published_at')->useCurrent();
            $table->text('body');
            $table->string('category');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blogposts');
    }
}
