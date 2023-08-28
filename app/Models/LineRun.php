<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineRun extends Model
{
    use HasFactory;

    protected $fillable = ['hash', 'line_segment_id'];
}
