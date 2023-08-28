<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineSegment extends Model
{
    use HasFactory;

    protected $fillable = ['reversible'];
    protected $casts = [
        'reversible' => 'boolean'
    ];
}
