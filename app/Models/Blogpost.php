<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blogpost extends Model
{
    protected $fillable = ['id', 'title', 'slug', 'author_name', 'twitter_handle', 'body', 'category'];
    protected $dates    = ['published_at'];
    protected $appends  = ['preview'];

    public function getPreviewAttribute(): string {
        return explode(".", $this->body)[0] . '.';
    }
}
