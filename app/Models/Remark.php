<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    use HasFactory;

    protected $fillable = ['text', 'type', 'code', 'summary'];
    protected $casts    = [
        'text'    => 'string',
        'type'    => 'string',
        'code'    => 'string',
        'summary' => 'string',
    ];
}
