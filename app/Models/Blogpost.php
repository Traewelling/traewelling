<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blogpost extends Model
{
    protected $dates = ['published_at', 'created_at', 'updated_at'];
}
